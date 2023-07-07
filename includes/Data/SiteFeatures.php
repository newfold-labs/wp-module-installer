<?php

namespace NewfoldLabs\WP\Module\Installer\Data;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginInstallTask;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginUninstallTask;
use NewfoldLabs\WP\Module\Installer\TaskManagers\PluginInstallTaskManager;
use NewfoldLabs\WP\Module\Installer\TaskManagers\PluginUninstallTaskManager;

/**
 * Class SiteFeatures
 */
final class SiteFeatures {

	/**
	 * Gets the list of plugins selected in the site features step
	 */
	public static function site_features_plugins( $onboarding_flow_data ) {

		if ( ! empty( $onboarding_flow_data ) && isset( $onboarding_flow_data['data'] ) ) {
			if ( isset( $onboarding_flow_data['data']['siteFeatures'] ) && count( $onboarding_flow_data['data']['siteFeatures'] ) > 0 ) {
				return $onboarding_flow_data['data']['siteFeatures'];
			}
		}

		return array();
	}

	/**
	 * Converts the allowed plugins to a path => slug map
	 * for faster queries
	 */
	public static function get_allowed_plugins() {
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
	public static function get_plugins_to_be_activated() {
		$plugins           = array();
		$installed_plugins = get_plugins();

		if ( isset( $installed_plugins ) ) {
			$allowed_plugins = self::get_allowed_plugins();

			foreach ( array_keys( $installed_plugins ) as $plugin_path ) {
				if ( isset( $allowed_plugins[ $plugin_path ] ) ) {
					$plugins[ $allowed_plugins[ $plugin_path ] ] = true;
				}
			}
		}
		return $plugins;
	}

	/**
	 * Installs/Uninstalls the requested site features (plugins).
	 */
	public static function set_site_features() {

		$onboarding_flow_data = \get_option( 'nfd_module_onboarding_flow', false );

		$site_features_plugins = self::site_features_plugins( $onboarding_flow_data );
		$plugins               = array_merge( self::get_plugins_to_be_activated(), $site_features_plugins );

		foreach ( $plugins as $plugin => $decision ) {
			if ( $decision ) {
				// If the Plugin exists and is activated
				if ( PluginInstaller::exists( $plugin, $decision ) ) {
					continue;
				}

				PluginInstallTaskManager::add_to_queue(
					new PluginInstallTask(
						$plugin,
						true
					)
				);
			} else {
				PluginUninstallTaskManager::add_to_queue(
					new PluginUninstallTask(
						$plugin
					)
				);
			}
		}

	}
}
