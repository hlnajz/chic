<?php

class Xoo_El_Helper extends Xoo_Helper{

	protected static $_instance = null;

	public static function get_instance( $slug, $path ){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $slug, $path );
		}
		return self::$_instance;
	}

	public function get_general_option( $subkey = '' ){
		return $this->get_option( 'xoo-el-gl-options', $subkey );
	}

	public function get_style_option( $subkey = '' ){
		return $this->get_option( 'xoo-el-sy-options', $subkey );
	}


	public function get_advanced_option( $subkey = '' ){
		return $this->get_option( 'xoo-el-av-options', $subkey );
	}

	//array( $field_id => $_FILES[id] )
	public function upload_files_as_attachment( $fieldsHavingFiles ){

		$attachmentIDS = array();

		if( !empty( $fieldsHavingFiles ) ){

			// These files need to be included as dependencies when on the front end.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			foreach ( $fieldsHavingFiles as $field_id => $files ) {

				foreach ( $files as $file ) {

					$_FILES = array( $field_id => $file );

					// Let WordPress handle the upload.
					// Remember, 'wpcfu_file' is the name of our file input in our form above.
					$attachment_id = media_handle_upload( $field_id, 0 );

					if ( is_wp_error( $attachment_id ) ) {
						
						//delete previously attached files
						foreach ($attachmentIDS as $field_id => $ids) {
							foreach ($ids as $id) {
								wp_delete_attachment( $id );
							}	
						}

						return new WP_Error( 'failed', __( 'Some files failed to upload', 'easy-login-woocommerce' ). ' - ' . $file['name'] . '('.$attachment_id->get_error_message().')' );
					} 
					else{
						$attachmentIDS[ $field_id ][] = $attachment_id;
					}
				}

			}

		}

		return $attachmentIDS;

	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public function is_request( $type ) {
		
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

}

function xoo_el_helper(){
	return Xoo_El_Helper::get_instance( 'easy-login-woocommerce', XOO_EL_PATH );
}
xoo_el_helper();

?>