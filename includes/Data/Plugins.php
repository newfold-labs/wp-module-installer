<?php
namespace NewfoldLabs\WP\Module\Installer\Data;

/**
 * List of Plugin Slugs/URLs/Domains
 */
final class Plugins {
	/**
	 * A value of true indicates that the slug/url/domain has been approved.
	 * A value of null indicates that the slug/url/domain has not been approved
	 * (or) has been temporarily deactivated.
	 *
	 * @var array
	 */
	protected static $wp_slugs = array(
		'jetpack'                           => array(
			'approved' => true,
			'path'     => 'jetpack/jetpack.php',
		),
		'woocommerce'                       => array(
			'approved' => true,
			'path'     => 'woocommerce/woocommerce.php',
		),
		'wordpress-seo'                     => array(
			'approved' => true,
			'path'     => 'wordpress-seo/wp-seo.php',
		),
		'wpforms-lite'                      => array(
			'approved' => true,
			'path'     => 'wpforms-lite/wpforms.php',
		),
		'google-analytics-for-wordpress'    => array(
			'approved' => true,
			'path'     => 'google-analytics-for-wordpress/googleanalytics.php',
		),
		'optinmonster'                      => array(
			'approved' => true,
			'path'     => 'optinmonster/optin-monster-wp-api.php',
		),
		'yith-woocommerce-ajax-search'      => array(
			'approved' => true,
			'path'     => 'yith-woocommerce-ajax-search/init.php',
		),
		'creative-mail-by-constant-contact' => array(
			'approved' => true,
			'path'     => 'creative-mail-by-constant-contact/creative-mail-plugin.php',
		),
	);

	/**
	 * Contains a list of zip url's with a unique "nfd_slug" for each.
	 *
	 * @var array
	 */
	protected static $nfd_slugs = array(
		'nfd_slug_endurance_page_cache'                  => array(
			'approved' => true,
			'url'      => 'https://raw.githubusercontent.com/bluehost/endurance-page-cache/production/endurance-page-cache.php',
			'path'     => WP_CONTENT_DIR . '/mu-plugins/endurance-page-cache.php',
		),
		'nfd_slug_yith_woocommerce_customize_myaccount_page' => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/yith-woocommerce-customize-myaccount-page',
			'path'     => 'yith-woocommerce-customize-myaccount-page-extended/init.php',
		),
		'nfd_slug_yith_woocommerce_gift_cards'           => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/yith-woocommerce-gift-cards',
			'path'     => 'yith-woocommerce-gift-cards-extended/init.php',
		),
		'nfd_slug_ecomdash_wordpress_plugin'             => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/ecomdash-wordpress-plugin',
			'path'     => 'ecomdash-wordpress-plugin/ecomdash-plugin.php',
		),
		'nfd_slug_yith_paypal_payments_for_woocommerce'  => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/yith-paypal-payments-for-woocommerce',
			'path'     => 'yith-paypal-payments-for-woocommerce-extended/init.php',
		),
		'nfd_slug_yith_shippo_shippings_for_woocommerce' => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/yith-shippo-shippings-for-woocommerce',
			'path'     => 'yith-shippo-shippings-for-woocommerce-extended/init.php',
		),
		'nfd_slug_yith_woocommerce_ajax_product_filter'  => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/yith-woocommerce-ajax-product-filter',
			'path'     => 'yith-woocommerce-ajax-product-filter-extended/init.php',
		),
		'nfd_slug_yith_woocommerce_booking'              => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/yith-woocommerce-booking',
			'path'     => 'yith-woocommerce-booking-extended/init.php',
		),
		'nfd_slug_yith_woocommerce_wishlist'             => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/yith-woocommerce-wishlist',
			'path'     => 'yith-woocommerce-wishlist-extended/init.php',
		),
		'nfd_slug_woo_razorpay'                          => array(
			'approved' => true,
			'url'      => 'https://hiive.cloud/workers/plugin-downloads/razorpay',
			'path'     => 'woo-razorpay/woo-razorpay.php',
		),
	);

	// [TODO] Think about deprecating this approach and move to nfd_slugs for url based installs.
	/**
	 * Contains a whitelist of zip url's.
	 *
	 * @var array
	 */
	protected static $urls = array(
		'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.8.5.3.zip' => true,
	);
	/**
	 * Contains a list of approved domains for zip based installs.
	 *
	 * @var array
	 */
	protected static $domains = array(
		'downloads.wordpress.org' => true,
		'nonapproveddomain.com'   => null,
	);

	/**
	 * Returns a list of whitelisted WordPress Plugin slugs.
	 *
	 * @return array
	 */
	public static function get_wp_slugs() {
		return self::$wp_slugs;
	}

	/**
	 * Returns a list of whitelisted Plugin URL's.
	 *
	 * @return array
	 */
	public static function get_urls() {
		return self::$urls;
	}

	/**
	 * Returns a list of whitelisted Plugin URL domains.
	 *
	 * @return array
	 */
	public static function get_domains() {
		return self::$domains;
	}

	/**
	 * Use this return value for a faster search of slug/url/domain.
	 *
	 * @return array
	 */
	public static function get() {
		return array(
			'wp_slugs'  => self::$wp_slugs,
			'nfd_slugs' => self::$nfd_slugs,
			'urls'      => self::$urls,
			'domains'   => self::$domains,
		);
	}

	/**
	 * Use this for finding the path for installed plugins.
	 *
	 * @return array
	 */
	public static function get_squashed() {
		return array_merge(
			array_filter( self::$wp_slugs, array( __CLASS__, 'check_approved' ) ),
			array_filter( self::$nfd_slugs, array( __CLASS__, 'check_approved' ) )
		);
	}

	/**
	 * Get approved slugs/urls/domains
	 *
	 * @return array
	 */
	public static function get_approved() {
		return array(
			'wp_slugs'  => array_keys( array_filter( self::$wp_slugs, array( __CLASS__, 'check_approved' ) ) ),
			'nfd_slugs' => array_keys( array_filter( self::$nfd_slugs, array( __CLASS__, 'check_approved' ) ) ),
			'urls'      => array_keys( self::$urls, true, true ),
			'domains'   => array_keys( self::$domains, true, true ),
		);
	}

	/**
	 * Checks if a Plugin slug has been approved.
	 *
	 * @param array $value The Plugin slug that will be checked.
	 * @return boolean
	 */
	private static function check_approved( $value ) {
		return true === $value['approved'];
	}

}
