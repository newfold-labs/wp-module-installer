<?php

namespace NewfoldLabs\WP\Module\Installer\WPAdmin\Listeners;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;

/**
 * Manages all the installer enqueue related functionalities for the module.
 */
class InstallerListener {

	/**
	 * Constructor for the Installer class.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_installer_script' ) );
	}

	/**
	 * Enqueues the installer script.
	 *
	 * @return void
	 */
	public function enqueue_installer_script() {
		$asset_file = NFD_INSTALLER_BUILD_DIR . '/installer.asset.php';

		if ( is_readable( $asset_file ) ) {

			$asset = include $asset_file;

			wp_register_script(
				'nfd-installer-enqueue',
				NFD_INSTALLER_BUILD_URL . '/installer.js',
				array_merge( $asset['dependencies'], array() ),
				$asset['version'],
				true
			);

			wp_register_style(
				'nfd-installer-enqueue',
				NFD_INSTALLER_BUILD_URL . '/installer.css',
				array(),
				$asset['version']
			);

			wp_add_inline_script(
				'nfd-installer-enqueue',
				'var nfdInstaller =' . wp_json_encode(
					value: array(
						'restUrl'           => \get_home_url() . '/index.php?rest_route=',
						'pluginInstallHash' => PluginInstaller::rest_get_plugin_install_hash(),
					)
				) . ';',
				'before'
			);


			wp_enqueue_script( 'nfd-installer-enqueue' );
			wp_enqueue_style( 'nfd-installer-enqueue' );
		}
	}
}
