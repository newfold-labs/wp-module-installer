<?php

namespace NewfoldLabs\WP\Module\Installer\Data;

use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Manages all the constants for the module.
 */
class Constants {
	/**
	 * Constructor for the Constants class.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( $container ) {
		if ( ! defined( 'NFD_INSTALLER_VERSION' ) ) {
			define( 'NFD_INSTALLER_VERSION', '1.4.3' );
		}
		if ( ! defined( 'NFD_INSTALLER_BUILD_DIR' ) && defined( 'NFD_INSTALLER_VERSION' ) ) {
			define( 'NFD_INSTALLER_BUILD_DIR', dirname( __DIR__, 2 ) . '/build/' . NFD_INSTALLER_VERSION );
		}
		if ( ! defined( 'NFD_INSTALLER_BUILD_URL' && defined( 'NFD_INSTALLER_VERSION' ) ) ) {
			define( 'NFD_INSTALLER_BUILD_URL', $container->plugin()->url . '/vendor/newfold-labs/wp-module-installer/build/' . NFD_INSTALLER_VERSION );
		}
	}
}
