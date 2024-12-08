<?php
/**
 * Cartflows Bricks Dynamic Data.
 *
 * @package Cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * This class handles bricks dynamic data functionality.
 */
class Cartflows_Bricks_Dynamic_Data {
	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 *
	 * @return object instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Setup actions and filters.
	 *
	 * @since 1.6.13
	 */
	private function __construct() {
		add_filter( 'bricks/dynamic_tags_list', array( $this, 'dynamic_tags' ), 10 );
		add_filter( 'bricks/dynamic_data/render_tag', array( $this, 'get_the_tag_value' ), 10, 2 );
		add_filter( 'bricks/frontend/render_data', array( $this, 'render' ), 10, 3 );
		add_filter( 'bricks/dynamic_data/render_content', array( $this, 'render' ), 10, 3 );

	}

	/**
	 * Register Tags.
	 *
	 * @param array $tags Existing tags.
	 *
	 * @return array
	 */
	public function dynamic_tags( $tags = array() ) {
		$cartflows_tags = apply_filters(
			'cartflows_bricks_dynamic_tags_list',
			array(
				array(
					'slug'  => 'wcf_next_step',
					'name'  => '{wcf_next_step}',
					'label' => esc_html__( 'Next Step', 'cartflows' ),
					'group' => esc_html__( 'CartFlows', 'cartflows' ),
				),
			)
		);
		return array_merge( $tags, $cartflows_tags );
	}

	/**
	 * Parse tag
	 *
	 * @param string $name    Tag name.
	 * @param object $post    Post object.
	 * @return string
	 */
	public function parse_tag( $name, $post ) {
		switch ( $name ) {
			case 'wcf_next_step':
				return '?class=wcf-next-step';
		}
		$name = apply_filters( 'cartflows_bricks_dynamic_data_render_tag', $name, $post );
		return $name;
	}

	/**
	 * Dynamic tag exists in $content: Replaces dynamic tag with requested data.
	 *
	 * @param string  $content Content.
	 * @param WP_Post $post   Post object.
	 * @param string  $context Context.
	 *
	 * @return string
	 */
	public function render( $content, $post, $context = 'text' ) {
		/**
		 * \w: Matches any word character (alphanumeric & underscore).
		 * Equivalent to [A-Za-z0-9_]
		 * "À-ÖØ-öø-ÿ" Add the accented characters
		 * "-" Needed because some post types handles are like "my-post-type"
		 * ":" Needed for extra arguments to dynamic data tags (e.g. post_excerpt:20 or wp_user_meta:my_meta_key)
		 * "|" and "," needed for the post terms like {post_terms_post_tag:sep} where sep could be a pipe or comma
		 * "(", ")" and "'" for the function arguments of the dynamic tag {echo}
		 * "@" to support email addresses as arguments of the dynamic tag {echo} #3kazphp
		 *
		 * @since 1.9.4: "u" modifier: Pattern strings are treated as UTF-8 to support Cyrillic, Arabic, etc.
		 * @since 1.10: "$", "+", "%", "#", "!", "=", "<", ">", "&", "~", "[", "]", ";" as arguments of the dynamic tag {echo}
		 *
		 * @see https://regexr.com/
		 */
		$pattern = '/{([\wÀ-ÖØ-öø-ÿ\-\s\.\/:\(\)\'@|,$%#!+=<>&~\[\];]+)}/u';

		/**
		 * Matches the echo tag pattern (#86bwebj6m)
		 *
		 * @since 1.9.8
		 */
		$echo_pattern = '/echo:([a-zA-Z0-9_]+)/';

		// Get a list of tags to exclude from the Dynamic Data logic.
		$exclude_tags = apply_filters( 'bricks/dynamic_data/exclude_tags', array() ); // phpcs:ignore

		/**
		 * STEP: Determine how many times we need to run the DD parser
		 *
		 * Previously we ran the parser by counting the number of open curly braces in the content. (@since 1.8)
		 * But this is not reliable because the content could contain curly braces in the code elements or any shortcodes.
		 * Causing the website to load extremely slow.
		 *
		 * @since 1.8.2 (#862jyyryg)
		 */
		// Get all registered tags except the excluded ones.
		// Example: [0 => "post_title", 1 => "woo_product_price", 2 => "echo"].
		$registered_tags = array_filter(
			array_column( $this->dynamic_tags(), 'slug' ),
			function ( $tag ) use ( $exclude_tags ) {
				return ! in_array( $tag, $exclude_tags, true );
			}
		);

		$dd_tags_in_content = array();
		$dd_tags_found      = array();
		$echo_tags_found    = array();

		// Find all dynamic data tags in the content.
		preg_match_all( $pattern, $content, $dd_tags_in_content );

		$dd_tags_in_content = ! empty( $dd_tags_in_content[1] ) ? $dd_tags_in_content[1] : array();

		// Find all echo tags in the content.
		preg_match_all( $echo_pattern, $content, $echo_tags_found );

		// Combine the dynamic data tags from the content and the echo tags.
		if ( ! empty( $echo_tags_found[0] ) ) {
			$dd_tags_in_content = array_merge( $dd_tags_in_content, $echo_tags_found[0] );
		}

		if ( ! empty( $dd_tags_in_content ) ) {
			/**
			 * $dd_tags_in_content only matches the pattern, but some codes from Code element could match the pattern too.
			 * Example: function test() { return 'Hello World'; } will match the pattern, but it's not a dynamic data tag.
			 *
			 * Find all dynamic data tags in the content which starts with dynamic data tag from $registered_tags
			 * Cannot use array_in or array_intersect because $registered_tags only contains the tag name, somemore tags could have filters like {echo:my_function( 'Hello World' )
			 *
			 * Example: $registered_tags    = [0 => "post_title", 1 => "woo_product_price", 2 => "echo"]
			 * Example: $dd_tags_in_content = [0 => "post_title", 1 => "woo_product_price:value", 2 => "echo:my_function('Hello World')"]
			 */
			$dd_tags_found = array_filter(
				$dd_tags_in_content,
				function ( $tag ) use ( $registered_tags ) {
					foreach ( $registered_tags as $all_tag ) {
						/**
						 * Skip not our tags.
						 */
						if ( strpos( $tag, 'wcf_' ) === 0 ) {
							return true;
						}

						if ( strpos( $tag, $all_tag ) === 0 ) {
							return true;
						}
					}
					return false;
				}
			);
		}

		// Get the count of found dynamic data tags.
		$dd_tag_count = count( $dd_tags_found );

		// STEP: Run the parser based on the count of found dynamic data tags.
		for ( $i = 0; $i < $dd_tag_count; $i++ ) {
			preg_match_all( $pattern, $content, $matches );

			if ( empty( $matches[0] ) ) {
				return $content;
			}

			foreach ( $matches[1] as $key => $match ) {
				$tag = $matches[0][ $key ];

				if ( in_array( $match, $exclude_tags, true ) ) {
					continue;
				}

				$value   = $this->get_the_tag_value( $match, $post );
				$content = str_replace( $tag, $value, $content );
			}
				++$dd_tag_count;
		}

		return $content;
	}

	/**
	 * Main function to render the tag value for WooCommerce provider
	 *
	 * @param string $tag    Tag name.
	 * @param object $post   Post object.
	 *
	 * @return string
	 */
	public function get_the_tag_value( $tag, $post ) {
		// Get all the registered tags and check if the tag exists.
		$registered_tags = $this->dynamic_tags();
		$tag_slug        = strtok( $tag, ':' );

		if ( false === array_search( $tag_slug, array_column( $registered_tags, 'slug' ), true ) ) {
			return $tag;
		}

		// Return the parsed tag value.
		return $this->parse_tag( $tag, $post );
	}

}

/**
 * Initiate the class.
 */
Cartflows_Bricks_Dynamic_Data::get_instance();
