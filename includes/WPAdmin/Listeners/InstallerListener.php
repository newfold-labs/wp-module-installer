<?php

namespace NewfoldLabs\WP\Module\Installer\WPAdmin\Listeners;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;
use NewfoldLabs\WP\Module\PLS\Utilities\PLSUtility;

/**
 * Manages all the installer enqueue related functionalities for the module.
 */
class InstallerListener {
	/**
	 * Identifier for script handle.
	 *
	 * @var string
	 */
	public static $handle = 'nfd-installer-enqueue';

	/**
	 * Constructor for the Installer class.
	 */
	public function __construct() {
		// Hook to enqueue installer scripts
		\add_action( 'newfold/installer/enqueue_scripts', array( $this, 'enqueue_installer_scripts' ) );
		\add_action( 'init', array( __CLASS__, 'load_text_domain' ), 100 );
		\add_filter( 'load_script_translation_file', array( $this, 'load_script_translation_file' ), 10, 3 );

		// Hook to listen to premium plugin activation
		$this->listen_for_premium_plugin_activation();
	}

	/**
	 * Enqueues all the installer scripts that are required.
	 * The Data Attribute Listener Script
	 * The Modal UI with React that installs the plugin
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

		if ( \is_readable( $asset_file ) ) {
			$asset = include $asset_file;

			\wp_register_script(
				'nfd-installer-data-attr-listener',
				NFD_INSTALLER_BUILD_URL . '/dataAttrListener.js',
				array_merge( $asset['dependencies'] ),
				$asset['version'],
				true
			);

			\wp_enqueue_script( 'nfd-installer-data-attr-listener' );
		}
	}

	/**
	 * Enqueues the installer script.
	 *
	 * @return void
	 */
	public function enqueue_installer_react_script() {
		$asset_file = NFD_INSTALLER_BUILD_DIR . '/installer.asset.php';

		if ( \is_readable( $asset_file ) ) {
			$asset = include $asset_file;

			\wp_register_script(
				self::$handle,
				NFD_INSTALLER_BUILD_URL . '/installer.js',
				array_merge( $asset['dependencies'], array() ),
				$asset['version'],
				true
			);

			\wp_register_style(
				self::$handle,
				NFD_INSTALLER_BUILD_URL . '/installer.css',
				array(),
				$asset['version']
			);

			\wp_add_inline_script(
				self::$handle,
				'var nfdInstaller = ' . \wp_json_encode(
					array(
						'restUrl'           => \get_home_url() . '/index.php?rest_route=',
						'pluginInstallHash' => PluginInstaller::rest_get_plugin_install_hash(),
					)
				) . ';',
				'before'
			);

			\wp_enqueue_script( self::$handle );
			\wp_enqueue_style( self::$handle );

			\wp_set_script_translations(
				self::$handle,
				'wp-module-installer',
				NFD_INSTALLER_DIR . '/languages'
			);
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
		$license_data_store = $pls_utility->retrieve_license_storage_map();

		if ( ! $license_data_store || empty( $license_data_store ) ) {
			return;
		}

		// Hook into activated_plugin action to trigger license activation after plugin activation
		\add_action(
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

	/**
	 * Load text domain for Module
	 *
	 * @return void
	 */
	public static function load_text_domain() {

		\load_plugin_textdomain(
			'wp-module-installer',
			false,
			NFD_INSTALLER_DIR . '/languages'
		);
	}

	/**
	 * Filters the file path for the JS translation JSON.
	 *
	 * If the script handle matches the module's handle, builds a custom path using
	 * the languages directory, current locale, text domain, and a hash of the script.
	 *
	 * @param string $file   Default translation file path.
	 * @param string $handle Script handle.
	 * @param string $domain Text domain.
	 * @return string Modified file path for the translation JSON.
	 */
	public function load_script_translation_file( $file, $handle, $domain ) {

		if ( $handle === self::$handle ) {
			$path   = NFD_INSTALLER_DIR . '/languages/';
			$locale = \determine_locale();

			$file_base = 'default' === $domain
				? $locale
				: $domain . '-' . $locale;
			$file      = $path . $file_base . '-' . md5( 'build/' . NFD_INSTALLER_VERSION . 'installer.js' )
						. '.json';

		}

		return $file;
	}
}
