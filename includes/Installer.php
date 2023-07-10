<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Installer\RestApi\RestApi;
use NewfoldLabs\WP\Module\Installer\TaskManagers\TaskManager;
use NewfoldLabs\WP\Module\Installer\Services\QueryParamService;

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

		// Handle Query Param tasks
		QueryParamService::handle_query_params();

		// Action to to activate all deactivated whitelisted existing plugins
		add_action( 'nfd_module_installer_plugin_cleanup_cron', array( QueryParamService::class, 'handle_plugin_cron_job' ) );

	}

}
