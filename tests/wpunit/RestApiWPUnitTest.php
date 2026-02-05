<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\RestApi\RestApi;

/**
 * RestApi wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Installer\RestApi\RestApi
 */
class RestApiWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Rest_api_init registers newfold-installer REST routes.
	 *
	 * Constructor adds register_routes to rest_api_init; firing the action registers routes.
	 *
	 * @return void
	 */
	public function test_rest_api_init_registers_installer_routes() {
		new RestApi();
		do_action( 'rest_api_init' );
		$server = rest_get_server();
		$routes = $server->get_routes();
		$found  = array_filter(
			array_keys( $routes ),
			function ( $route ) {
				return strpos( $route, 'newfold-installer' ) !== false;
			}
		);
		$this->assertNotEmpty( $found, 'Expected newfold-installer routes to be registered' );
	}
}
