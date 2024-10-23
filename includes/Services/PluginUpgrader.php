<?php
namespace NewfoldLabs\WP\Module\Installer\Services;

use NewfoldLabs\WP\Module\Installer\Data\Plugins;
use NewfoldLabs\WP\Module\PLS\Utilities\PLSUtility;

/**
 * Class PluginUpgrader
 *
 * This class is responsible for upgrading plugins.
 */
class PluginUpgrader {

	/**
	 * Tries to upgrade the extended YITH plugins to their corresponding premium versions.
	 *
	 * @return array
	 */
	public static function upgrade_extended_yith_plugins() {
		// Define the list of extended YITH plugins to upgrade.
		$yith_plugins_to_upgrade = array(
			'nfd_slug_yith_woocommerce_ajax_product_filter',
			'nfd_slug_yith_woocommerce_wishlist',
			'nfd_slug_yith_woocommerce_booking',
			'nfd_slug_yith_woocommerce_gift_cards',
			'nfd_slug_yith_woocommerce_customize_myaccount_page',
			'yith-woocommerce-ajax-search',
		);

		// Array to store the status of each plugin's upgrade process
		$yith_plugins_upgrade_status = array();

		foreach ( $yith_plugins_to_upgrade as $index => $extended_slug ) {
			$upgrade_status                                = self::upgrade_extended_yith_plugin( $extended_slug );
			$yith_plugins_upgrade_status[ $extended_slug ] = $upgrade_status;
		}

		return $yith_plugins_upgrade_status;
	}

	/**
	 * Upgrades a single extended YITH plugin to its corresponding premium version.
	 *
	 * @param string $extended_slug The slug of the extended plugin.
	 *
	 * @return array Contains the status of the upgrade process with 'upgraded' and 'message' keys.
	 */
	public static function upgrade_extended_yith_plugin( $extended_slug ) {
		// Define the list of extended YITH plugins and their corresponding premium versions.
		$yith_plugins_to_upgrade = array(
			'nfd_slug_yith_woocommerce_ajax_product_filter' => 'yith-woocommerce-ajax-navigation',
			'nfd_slug_yith_woocommerce_wishlist'   => 'yith-woocommerce-wishlist',
			'nfd_slug_yith_woocommerce_booking'    => 'yith-woocommerce-booking',
			'nfd_slug_yith_woocommerce_gift_cards' => 'yith-woocommerce-gift-cards',
			'nfd_slug_yith_woocommerce_customize_myaccount_page' => 'yith-woocommerce-customize-myaccount-page',
			'yith-woocommerce-ajax-search'         => 'yith-woocommerce-ajax-search',
		);

		// Initialize status array for the plugin upgrade process
		$upgrade_status = array(
			'upgraded' => false,
			'message'  => '',
		);

		// Get plugin status codes for comparison
		$status_codes = Plugins::get_status_codes();

		// Check if the extended plugin exists in the list
		if ( ! isset( $yith_plugins_to_upgrade[ $extended_slug ] ) ) {
			$upgrade_status['message'] = __( 'Invalid extended plugin slug provided.', 'wp-module-installer' );
			return $upgrade_status;
		}

		// Get the corresponding premium plugin slug
		$premium_slug = $yith_plugins_to_upgrade[ $extended_slug ];

		// Get the status of the extended plugin
		$extended_status = PluginInstaller::get_plugin_status( $extended_slug );

		// Check if the extended plugin's status is unknown or not installed
		if ( $status_codes['unknown'] === $extended_status ) {
			$upgrade_status['message'] = __( 'Could not determine the status of the extended plugin.', 'wp-module-installer' );
			return $upgrade_status;
		}

		if ( $status_codes['not_installed'] === $extended_status ) {
			$upgrade_status['message'] = __( 'Extended plugin is not installed.', 'wp-module-installer' );
			return $upgrade_status;
		}

		// Check if the premium plugin should be activated after installation
		$should_activate = ( $status_codes['active'] === $extended_status );

		// Deactivate the extended version of the plugin if the premium plugin needs to be activated
		if ( $should_activate ) {
			$deactivation_response = PluginInstaller::deactivate( $extended_slug );
			if ( is_wp_error( $deactivation_response ) ) {
				$upgrade_status['message'] = __( 'Failed to deactivate the extended plugin: ', 'wp-module-installer' ) . $extended_slug;
				return $upgrade_status;
			}
		}

		// Use the install_premium_plugin function to install and activate the premium plugin
		$install_status = PluginInstaller::install_premium_plugin( $premium_slug, 'yith', $should_activate );
		if ( is_wp_error( $install_status ) ) {
			$upgrade_status['message'] = $install_status->get_error_message();

			// Reactivate the extended plugin if premium installation failed and it was deactivated
			if ( $should_activate ) {
				$reactivation_response = PluginInstaller::activate( $extended_slug );
				if ( is_wp_error( $reactivation_response ) ) {
					$upgrade_status['message'] .= __( ' Also Failed to reactivate the extended plugin: ', 'wp-module-installer' ) . $extended_slug;
				}
			}

			return $upgrade_status;
		}

		// Mark as successfully upgraded
		$upgrade_status['upgraded'] = true;
		$upgrade_status['message']  = __( 'Successfully provisioned and installed: ', 'wp-module-installer' ) . $premium_slug;

		// Attempt to uninstall the extended version of the plugin
		$uninstall_status = PluginUninstaller::uninstall( $extended_slug );
		if ( is_wp_error( $uninstall_status ) ) {
			$upgrade_status['message'] = __( 'Successfully installed the premium plugin, but there was an error uninstalling the extended plugin. Manual cleanup may be required.', 'wp-module-installer' );
			return $upgrade_status;
		}

		// Return the status of the upgrade process
		return $upgrade_status;
	}
}
