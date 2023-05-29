<?php

namespace NewfoldLabs\WP\Module\Installer\RestApi;

/**
 * Instantiate controllers and register routes.
 */
final class RestApi {

	/**
	 * List of controllers to register.
	 *
	 * @var array
	 */
	protected $controllers = array(
		'NewfoldLabs\\WP\\Module\\Installer\\RestApi\\PluginsController',
		'NewfoldLabs\\WP\\Module\\Installer\\RestApi\\ThemeInstallerController',
	);

	/**
	 * The constructor that registers all the controllers.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Helper to register all the controller.
	 *
	 * @return void
	 */
	public function register_routes() {
		foreach ( $this->controllers as $controller ) {
			/**
			 * Get an instance of the WP_REST_Controller.
			 *
			 * @var $instance WP_REST_Controller
			 */
			$instance = new $controller();
			$instance->register_routes();
		}
	}
}
