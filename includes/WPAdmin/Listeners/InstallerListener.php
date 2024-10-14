<?php

namespace NewfoldLabs\WP\Module\Installer\WPAdmin\Listeners;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;
use NewfoldLabs\WP\Module\PLS\Utilities\PLSUtility;

/**
 * Manages all the installer enqueue related functionalities for the module.
 */
class InstallerListener {

	/**
	 * Constructor for the Installer class.
	 */
	public function __construct() {
		// Hook to enqueue installer scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_installer_script' ) );

		// Hook to listen to premium plugin activation
		$this->listen_for_premium_plugin_activation();
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

	/**
	 * Listens for premium plugin activation using activated_plugin hook.
	 *
	 * @return void
	 */
	private function listen_for_premium_plugin_activation() {
		$pls_utility = new PLSUtility();

		// Retrieve the license data (decrypted) from the option
		$license_data_store = $pls_utility->retrieve_license_data();

		if ( ! $license_data_store || empty( $license_data_store ) ) {
			return;
		}

		// Hook into activated_plugin action to trigger license activation after plugin activation
		add_action(
			'activated_plugin',
			function ( $plugin, $network_wide ) use ( $pls_utility, $license_data_store ) {
				foreach ( $license_data_store as $plugin_slug => $license_data ) {
					if ( isset( $license_data['basename'] ) && $license_data['basename'] === $plugin ) {
						$pls_utility->activate_license( $plugin_slug );
						break;
					}
				}
			},
			10,
			2
		);
	}
}
