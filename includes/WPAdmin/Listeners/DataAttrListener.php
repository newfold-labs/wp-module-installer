<?php

namespace NewfoldLabs\WP\Module\Installer\WPAdmin\Listeners;

/**
 * Manages all the data-* listening related functionalities for the module.
 */
class DataAttrListener {
	/**
	 * Constructor for the DataAttrListener class.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_data_attr_listener' ) );
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

			wp_add_inline_script(
				'nfd-installer-data-attr-listener',
				'var nfdInstallerDataAttrListener =' . wp_json_encode(
					array(
						'restUrl' => \get_home_url() . '/index.php?rest_route=',
					)
				) . ';',
				'before'
			);

			wp_enqueue_script( 'nfd-pls-data-attr-installer' );
		}
	}
}
