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

			self::deactivate_plugin_if_active( $plugin_path );
			// $deleted = self::delete_plugin( $plugin_path );

			// // If Deletion is not successful throw an error for a retry.
			// if ( is_wp_error( $deleted ) ) {
			// return $deleted;
			// }
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

	/**
	 * Establishes a connection to the wp_filesystem.
	 *
	 * @return boolean
	 */
	protected static function connect_to_filesystem() {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// We want to ensure that the user has direct access to the filesystem.
		$access_type = \get_filesystem_method();
		if ( 'direct' !== $access_type ) {
			return false;
		}

		$creds = \request_filesystem_credentials( site_url() . '/wp-admin', '', false, false, array() );

		if ( ! \WP_Filesystem( $creds ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Deletes a Plugin if it exists.
	 *
	 * @param string $plugin_path Path to the plugin's header file.
	 * @return boolean|\WP_Error
	 */
	public static function delete_plugin( $plugin_path ) {

		// Checks if the necessary functions exist
		if ( ! function_exists( 'delete_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! self::connect_to_filesystem() ) {
			return new \WP_Error(
				'nfd_installer_error',
				'Could not connect to the filesystem.',
				array( 'status' => 500 )
			);
		}

		// Removes directory and files of a plugin
		$deleted = \delete_plugins( array( $plugin_path ) );
		if ( ! $deleted || is_wp_error( $deleted ) ) {
			return new \WP_Error(
				'nfd_installer_error',
				'Unable to Delete the Plugin',
				array( 'status' => 500 )
			);
		}

		return true;
	}

}
