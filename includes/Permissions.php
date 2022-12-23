<?php
namespace NewfoldLabs\WP\Module\Installer;

/**
 * Permissions and Authorization constants and utilities.
 */
final class Permissions {

	public static function rest_get_plugin_install_hash() {
		return 'NFD_INSTALLER_' . hash( 'sha256', NFD_INSTALLER_VERSION . wp_salt( 'nonce' ) . site_url() );
	}

	public static function rest_verify_plugin_install_hash( $hash ) {
		return $hash === self::rest_get_plugin_install_hash();
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

