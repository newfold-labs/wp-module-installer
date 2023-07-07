<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\Data\SiteFeatures;
use NewfoldLabs\WP\Module\Installer\RestApi\RestApi;
use NewfoldLabs\WP\Module\Installer\TaskManagers\TaskManager;
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

		// Check if the current URL contains enable_site_features flag
		if ( isset( $_GET['enable_site_features'] ) && $_GET['enable_site_features'] === 'true' ) {
			SiteFeatures::set_site_features();
		}
	}

}
