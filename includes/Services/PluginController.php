<?php
namespace NewfoldLabs\WP\Module\Installer\Services;

use NewfoldLabs\WP\Module\Installer\Data\Plugins;

/**
 * Class PluginController
 * This class is responsible to activate/deactivate a specified plugin
 */
class PluginController {

	/**
	 * Activate/Deactivate a specific plugin if active.
	 *
	 * @param string  $plugin Plugin URL.
	 * @param boolean $activate Activate or deactivate the plugin.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function control( $plugin, $activate ) {

		$plugin_list = Plugins::get_squashed();
		// Gets the specified path for the Plugin from the predefined list
		$plugin_path = $plugin_list[ $plugin ]['path'];

		if ( isset( $plugin_path ) && self::is_plugin_installed( $plugin_path ) ) {

			if ( true === $activate && ! is_plugin_active( $plugin_path ) ) {
				// Checks if the necessary functions exist
				if ( ! function_exists( 'activate_plugin' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				\activate_plugin( $plugin_path );

			} elseif ( false === $activate && is_plugin_active( $plugin_path ) ) {
				self::deactivate_plugin_if_active( $plugin_path );
			}
		}

		return new \WP_REST_Response(
			array(),
			201
		);
	}

	/**
	 * Checks if a plugin with the given slug exists.
	 *
	 * @param string $plugin Plugin
	 * @return boolean
	 */
	public static function exists( $plugin ) {

		$plugin_list = Plugins::get_squashed();
		$plugin_path = $plugin_list[ $plugin ]['path'];

		if ( ! self::is_plugin_installed( $plugin_path ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Determines if a plugin has already been installed.
	 *
	 * @param string $plugin_path Path to the plugin's header file.
	 * @return boolean
	 */
	public static function is_plugin_installed( $plugin_path ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = \get_plugins();
		if ( ! empty( $all_plugins[ $plugin_path ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Deactivates a Plugin if it is activated.
	 *
	 * @param string $plugin_path Path to the plugin's header file.
	 */
	public static function deactivate_plugin_if_active( $plugin_path ) {

		// Checks if the necessary functions exist
		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Determines whether a plugin is active.
		if ( \is_plugin_active( $plugin_path ) ) {
			// Deactivates a single plugin or multiple plugins.
			// /wp-admin/includes/plugin.php
			\deactivate_plugins( $plugin_path );
		}
	}
}
