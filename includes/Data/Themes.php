<?php
namespace NewfoldLabs\WP\Module\Installer\Data;

/**
 * Class containing Theme related data.
 */
final class Themes {
	/*
	A value of true indicates that the slug has been approved.
	A value of null indicates that the slug has not been approved
	(or) has been temporarily deactivated.
	*/

	/**
	 * Contains a list of zipped theme url's with a unique "nfd_slug" for each.
	 *
	 * @var array
	 */
	protected static $nfd_slugs = array(
		'nfd_slug_yith_wonder' => array(
			'approved'   => true,
			'url'        => 'https://hiive.cloud/workers/plugin-downloads/yith-wonder-theme',
			'stylesheet' => 'yith-wonder',
		),
	);

	/**
	 * Use this return value for a faster search of slugs.
	 *
	 * @return array
	 */
	public static function get() {
		return array(
			'nfd_slugs' => self::$nfd_slugs,
		);
	}

	/**
	 * Get approved theme slugs.
	 *
	 * @return array
	 */
	public static function get_approved() {
		return array(
			'nfd_slugs' => array_keys( array_filter( self::$nfd_slugs, array( __CLASS__, 'check_approved' ) ) ),
		);
	}
	/**
	 * Checks if $value has been approved.
	 *
	 * @param array $value The value to check for.
	 * @return boolean
	 */
	private static function check_approved( $value ) {
		return true === $value['approved'];
	}
}
