<?php
namespace NewfoldLabs\WP\Module\Installer\WPCLI\Handlers;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;
use NewfoldLabs\WP\Module\Installer\Services\PluginUpgrader;
use WP_CLI;

/**
 * Class InstallerCommandHandler
 *
 * Handles WP-CLI custom commands for the Installer module.
 */
class InstallerCommandHandler {
	/**
	 * Triggers the upgrade of a set of pre-defined extended YITH plugins.
	 *
	 * This command runs the upgrade process for YITH plugins, upgrading them from extended versions
	 * to their corresponding premium versions, and outputs the status of each upgrade in JSON format.
	 *
	 * ## EXAMPLES
	 *
	 *     wp installer auto_upgrade_extended_yith_plugins
	 *
	 * @return void
	 */
	public function auto_upgrade_extended_yith_plugins() {
		$all_upgrades_successful = true;
		$plugin_upgrade_statuses = PluginUpgrader::upgrade_extended_yith_plugins();
		foreach ( $plugin_upgrade_statuses as $plugin_slug => $status_info ) {
			if ( ! $status_info['upgraded'] ) {
				$all_upgrades_successful = false;
			}

			$status_json = wp_json_encode(
				array(
					'slug'    => $plugin_slug,
					'status'  => $status_info['upgraded'],
					'message' => $status_info['message'],
				)
			);

			WP_CLI::log( $status_json );
		}

		if ( $all_upgrades_successful ) {
			WP_CLI::success( __( 'YITH plugin upgrade process completed successfully.', 'wp-module-installer' ) );
		} else {
			WP_CLI::error( __( 'YITH plugin upgrade process completed, but some upgrades failed. Please check the logs.', 'wp-module-installer' ) );
		}
	}

	/**
	 * Triggers the upgrade of a single extended YITH plugin.
	 *
	 * This command upgrades a specific YITH plugin from its extended version
	 * to its corresponding premium version and outputs the result in JSON format.
	 *
	 * ## EXAMPLES
	 *
	 *     wp installer upgrade_extended_yith_plugin <extended_yith_plugin_slug>
	 *
	 * @param array $args Arguments passed from the command line. First argument is the plugin slug.
	 *
	 * @return void
	 */
	public function upgrade_extended_yith_plugin( $args ) {
		$plugin_slug = $args[0];

		$status_info = PluginUpgrader::upgrade_extended_yith_plugin( $plugin_slug );
		$status_json = wp_json_encode(
			array(
				'slug'    => $plugin_slug,
				'status'  => $status_info['upgraded'],
				'message' => $status_info['message'],
			)
		);
		WP_CLI::log( $status_json );

		if ( $status_info['upgraded'] ) {
			WP_CLI::success( 'Plugin upgrade completed successfully.' );
		} else {
			WP_CLI::error( 'Plugin upgrade failed. Please check the logs for more details.' );
		}
	}

	/**
	 * Triggers the installation and activation of a premium plugin.
	 *
	 * This command provisions a license, installs the premium plugin, and optionally activates it
	 * based on the activation parameter passed. It outputs the status of the process.
	 *
	 * ## OPTIONS
	 *
	 * <premium_slug>
	 * : The slug of the premium plugin to be installed.
	 *
	 * <provider>
	 * : The name of the provider for the premium plugin.
	 *
	 * [--activate]
	 * : Optional flag to activate the plugin after installation.
	 *
	 * ## EXAMPLES
	 *
	 *     wp installer install_premium_plugin <premium_slug> <provider> --activate
	 *     wp installer install_premium_plugin <premium_slug> <provider>
	 *
	 * @param array $args Arguments passed from the command line. First argument is the plugin slug.
	 * @param array $assoc_args Associative arguments (like --activate).
	 *
	 * @return void
	 */
	public function install_premium_plugin( $args, $assoc_args ) {
		$premium_slug = $args[0];
		$provider     = $args[1];
		$activate     = isset( $assoc_args['activate'] );

		// Ensure both the plugin slug and provider are not empty
		if ( empty( $premium_slug ) || empty( $provider ) ) {
			WP_CLI::error( __( 'Both plugin slug and provider name are required.', 'wp-module-installer' ) );
			return;
		}

		// Call the function to provision, install, and (optionally) activate the premium plugin
		$status = PluginInstaller::install_premium_plugin( $premium_slug, $provider, $activate );

		// Handle error or success response
		if ( is_wp_error( $status ) ) {
			WP_CLI::error( $status->get_error_message() );
		} else {
			WP_CLI::success( $status->get_data()['message'] );
		}
	}
}
