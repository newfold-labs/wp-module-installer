<?php
namespace NewfoldLabs\WP\Module\Installer;

/**
 * Permissions and Authorization constants and utilities.
 */
final class Permissions {

	/**
	 * WordPress Admin capability string
	 */
	const ADMIN          = 'manage_options';

	/**
	 * Retrieve Plugin Install Hash Value.
	 *
	 * @return string
	 */
	public static function rest_get_plugin_install_hash() {
		return 'NFD_INSTALLER_' . hash( 'sha256', NFD_INSTALLER_VERSION . wp_salt( 'nonce' ) . site_url() );
	}

	/**
	 * Verify Plugin Install Hash Value.
	 *
	 * @param string $hash Hash Value.
	 * @return boolean
	 */
	public static function rest_verify_plugin_install_hash( $hash ) {
		return self::rest_get_plugin_install_hash() === $hash;
	}

	/**
	 * Confirm REST API caller has ADMIN user capabilities.
	 *
	 * @return boolean
	 */
	public static function rest_is_authorized_admin() {
		return \is_user_logged_in() && \current_user_can( self::ADMIN );
	}

}

