<?php

namespace NewfoldLabs\WP\Module\Installer\Services;

use NewfoldLabs\WP\Module\Installer\TaskManagers\PluginInstallTaskManager;

/**
 * Class QueryParamService
 */
final class QueryParamService {

	public static function handle_query_params() {
		if ( isset( $_GET['enable_site_features'] ) && 'true' === $_GET['enable_site_features'] ) {
			// Change the activation criteria for the plugins in the queue. 
			PluginInstallTaskManager::requeue_with_changed_activation();
		}
	}
}
