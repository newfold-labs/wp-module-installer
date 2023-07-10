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
	 * Converts the allowed plugins to a path => slug map
	 * for faster queries
	 */
	private static function get_allowed_plugins() {
		$allowed_plugins   = array();
		$installed_plugins = Plugins::get_squashed();
		foreach ( $installed_plugins as $plugin_name => $plugin_data ) {
			$allowed_plugins[ $plugin_data['path'] ] = $plugin_name;
		}
		return $allowed_plugins;
	}

	/**
	 * Gets the list of allowed plugins to be activated.
	 */
	private static function get_plugins_to_be_activated() {
		$plugins           = array();
		$installed_plugins = get_plugins();

		if ( isset( $installed_plugins ) ) {
			$allowed_plugins = self::get_allowed_plugins();

			foreach ( array_keys( $installed_plugins ) as $plugin_path ) {
				if ( ! is_plugin_active( $plugin_path ) && isset( $allowed_plugins[ $plugin_path ] ) ) {
					$plugins[ $allowed_plugins[ $plugin_path ] ] = true;
				}
			}
		}
		return $plugins;
	}

	/**
	 * Setup Cron to run after 30mins and do a second check
	 * for plugin installation and activations
	 */
	public static function setup_onboarding_plugin_install_cron() {

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_module_installer_onboarding_cleanup_cron' ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'nfd_module_installer_onboarding_cleanup_cron' );

		}
	}

	/**
	 * Handles the cron call and cleanup
	 */
	public static function handle_cron_job() {

		// Fetch all the plugins and add the ones that are not active
		$plugins = self::get_plugins_to_be_activated();
		foreach ( $plugins as $plugin => $decision ) {
			PluginInstallTaskManager::add_to_queue(
				new PluginInstallTask(
					$plugin,
					true
				)
			);
		}
		wp_clear_scheduled_hook( 'nfd_module_installer_onboarding_cleanup_cron' );
	}


	public static function handle_query_params() {
		if ( isset( $_GET['enable_site_features'] ) && 'true' === $_GET['enable_site_features'] ) {
			// Change the activation criteria for the plugins in the queue.
			PluginInstallTaskManager::requeue_with_changed_activation();
			self::setup_onboarding_plugin_install_cron();
		}
	}
}
