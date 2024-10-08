<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\RestApi\RestApi;
use NewfoldLabs\WP\Module\Installer\WPAdmin\WPAdmin;
use NewfoldLabs\WP\Module\Installer\TaskManagers\TaskManager;
use NewfoldLabs\WP\Module\Installer\WPCLI\WPCLI;
use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * The Module's main class.
 */
class Installer {

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( Container $container ) {

		$this->container = $container;

		// Module functionality goes here

		new RestApi();

		new TaskManager();

		new WPCLI();

		if ( Permissions::rest_is_authorized_admin() ) {
			new WPAdmin();
		}
	}
}
