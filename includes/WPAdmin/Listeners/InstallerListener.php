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
		add_action( 'newfold/installer/enqueue_scripts', array( $this, 'enqueue_installer_scripts' ) );
	}

	/**
	 * Enqueues all the installer scripts that are required.
	 *
	 * @return void
	 */
	public function enqueue_installer_scripts() {
		$this->enqueue_data_attr_listener();
		$this->enqueue_installer_react_script();
	}

	/**
	 * Enqueues the data-* attribute listener script.
	 *
	 * @return void
	 */
	public function enqueue_data_attr_listener() {
		$asset_file = NFD_INSTALLER_BUILD_DIR . '/dataAttrListener.asset.php';

		if ( is_readable( $asset_file ) ) {
			$asset = include $asset_file;

			wp_register_script(
				'nfd-installer-data-attr-listener',
				NFD_INSTALLER_BUILD_URL . '/dataAttrListener.js',
				array_merge( $asset['dependencies'], array() ),
				$asset['version'],
				true
			);

			wp_enqueue_script( 'nfd-installer-data-attr-listener' );
		}
	}

	/**
	 * Enqueues the installer react scripts.
	 *
	 * @return void
	 */
	public function enqueue_installer_react_script() {
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
