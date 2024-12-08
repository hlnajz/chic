<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers;

use Automattic\WooCommerce\GoogleListingsAndAds\API\TransportMethods;
use Automattic\WooCommerce\GoogleListingsAndAds\HelperTraits\GTINMigrationUtilities;
use Automattic\WooCommerce\GoogleListingsAndAds\Jobs\MigrateGTIN;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\RESTServer;
use Exception;
use WP_REST_Request as Request;
use WP_REST_Response as Response;

defined( 'ABSPATH' ) || exit;

/**
 * Class GTINMigrationController offering API endpoint for GTIN field Migration
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers
 */
class GTINMigrationController extends BaseController {
	use EmptySchemaPropertiesTrait;
	use GTINMigrationUtilities;

	/**
	 * Job responsible to run the migration in the background.
	 *
	 * @var MigrateGTIN
	 */
	protected $job;

	/**
	 * Constructor.
	 *
	 * @param RESTServer  $server
	 * @param MigrateGTIN $job
	 */
	public function __construct( RESTServer $server, MigrateGTIN $job ) {
		parent::__construct( $server );
		$this->job = $job;
	}

	/**
	 * Register rest routes with WordPress.
	 */
	public function register_routes(): void {
		$this->register_route(
			'gtin-migration',
			[
				[
					'methods'             => TransportMethods::CREATABLE,
					'callback'            => $this->start_migration_callback(),
					'permission_callback' => $this->get_permission_callback(),
					'args'                => $this->get_schema_properties(),
				],
				[
					'methods'  => TransportMethods::READABLE,
					'callback' => $this->get_migration_status_callback(),
				],
				'schema' => $this->get_api_response_schema_callback(),
			]
		);
	}


	/**
	 * Callback function for scheduling GTIN migration job.
	 *
	 * @return callable
	 */
	protected function start_migration_callback(): callable {
		return function ( Request $request ) {
			try {
				if ( ! $this->job->can_schedule( [ 1 ] ) ) {
					return new Response(
						[
							'status'  => 'error',
							'message' => __( 'GTIN Migration cannot be scheduled.', 'google-listings-and-ads' ),
						],
						400
					);
				}

				$this->job->schedule();
				return new Response(
					[
						'status'  => 'success',
						'message' => __( 'GTIN Migration successfully started.', 'google-listings-and-ads' ),
					],
					200
				);
			} catch ( Exception $e ) {
				return $this->response_from_exception( $e );
			}
		};
	}

	/**
	 * Callback function for getting the current migration status.
	 *
	 * @return callable
	 */
	protected function get_migration_status_callback(): callable {
		return function () {
			return new Response(
				[
					'status' => $this->get_gtin_migration_status(),
				],
				200
			);
		};
	}

	/**
	 * Get Schema title
	 *
	 * @return string
	 */
	protected function get_schema_title(): string {
		return 'gtin_migration';
	}
}
