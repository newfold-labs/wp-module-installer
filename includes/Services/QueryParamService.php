<?php

namespace NewfoldLabs\WP\Module\Installer\Services;

use NewfoldLabs\WP\Module\Installer\Data\Plugins;
use NewfoldLabs\WP\Module\Installer\TaskManagers\PluginInstallTaskManager;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginInstallTask;

/**
 * Class QueryParamService
 */
class QueryParamService {

	/**
	 * Converts the whitelisted plugins list to a hashmap
	 */
	private static function get_allowed_plugins() {
		$allowed_plugins   = array();
		$installed_plugins = Plugins::get_squashed();

		/**
		 * Converting the list into a hashmap of plugin_path => slug
		 * for faster queries
		 */
		foreach ( $installed_plugins as $plugin_name => $plugin_data ) {
			$allowed_plugins[ $plugin_data['path'] ] = $plugin_name;
		}
		return $allowed_plugins;
	}

	/**
	 * Gets the list of whitelisted plugins to be activated.
	 */
	private static function get_plugins_to_be_activated() {
		$plugins = array();
		// Get all the active plugins on the site
		$installed_plugins = get_plugins();

		if ( isset( $installed_plugins ) ) {
			// Get the list of whitelisted plugins by nfd
			$allowed_plugins = self::get_allowed_plugins();

			foreach ( array_keys( $installed_plugins ) as $plugin_path ) {
				// Add them only if whitelisted and inactive
				if ( ! is_plugin_active( $plugin_path ) && isset( $allowed_plugins[ $plugin_path ] ) ) {
					$plugins[ $allowed_plugins[ $plugin_path ] ] = true;
				}
			}
		}
		return $plugins;
	}

	/**
	 * Setup Cron to run after 30mins and do a preactionary check
	 * for activation of installed plugins.
	 */
	public static function setup_onboarding_plugin_install_cron() {

		// Register the one time cron task if not already scheduled
		if ( ! wp_next_scheduled( 'nfd_module_installer_plugin_cleanup_cron' ) ) {
			wp_schedule_single_event( time() + 30 * MINUTE_IN_SECONDS, 'nfd_module_installer_plugin_cleanup_cron' );

		}
	}

	/**
	 * Handles the cron call and cleanup
	 */
	public static function handle_plugin_cron_job() {

		/**
		 * Get all the whitelisted plugins on the site
		 * which are installed by nfd but not activated yet.
		 */
		$plugins = self::get_plugins_to_be_activated();
		foreach ( $plugins as $plugin => $decision ) {
			PluginInstallTaskManager::add_to_queue(
				new PluginInstallTask(
					$plugin,
					$decision
				)
			);
		}
		// Clear the cron to prevent repeated execution
		wp_clear_scheduled_hook( 'nfd_module_installer_plugin_cleanup_cron' );
	}

	/**
	 * Listen for Query Param and perform specfic tasks
	 */
	public static function handle_query_params() {

		/**
		 * Check for enable_site_features to update the queue
		 * with activation true for whitelisted plugins
		 */
		if ( isset( $_GET['enable_site_features'] ) && 'true' === $_GET['enable_site_features'] ) {
			// // Change the activation criteria to true for the plugins in the queue.
			// PluginInstallTaskManager::requeue_with_changed_activation();

			// Start a one time Cron Job to activate all deactivated plugins
			self::setup_onboarding_plugin_install_cron();
		}
	}
}
