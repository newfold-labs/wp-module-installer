<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\Data\Plugins;

/**
 * Data Plugins wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Installer\Data\Plugins
 */
class DataPluginsWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Get_wp_slugs returns array with expected plugin slugs.
	 *
	 * @return void
	 */
	public function test_get_wp_slugs_returns_expected_slugs() {
		$slugs = Plugins::get_wp_slugs();
		$this->assertArrayHasKey( 'jetpack', $slugs );
		$this->assertArrayHasKey( 'woocommerce', $slugs );
		$this->assertArrayHasKey( 'wordpress-seo', $slugs );
		$this->assertArrayHasKey( 'wpforms-lite', $slugs );
	}

	/**
	 * Get_status_codes returns expected status codes.
	 *
	 * @return void
	 */
	public function test_get_status_codes_returns_expected_codes() {
		$codes = Plugins::get_status_codes();
		$this->assertSame( 'unknown', $codes['unknown'] );
		$this->assertSame( 'installed', $codes['installed'] );
		$this->assertSame( 'active', $codes['active'] );
		$this->assertSame( 'not_installed', $codes['not_installed'] );
	}

	/**
	 * Get returns array with wp_slugs, nfd_slugs, urls, domains, status_codes.
	 *
	 * @return void
	 */
	public function test_get_returns_all_sections() {
		$data = Plugins::get();
		$this->assertArrayHasKey( 'wp_slugs', $data );
		$this->assertArrayHasKey( 'nfd_slugs', $data );
		$this->assertArrayHasKey( 'urls', $data );
		$this->assertArrayHasKey( 'domains', $data );
		$this->assertArrayHasKey( 'status_codes', $data );
	}

	/**
	 * Get_approved returns arrays of approved keys.
	 *
	 * @return void
	 */
	public function test_get_approved_returns_approved_keys() {
		$approved = Plugins::get_approved();
		$this->assertArrayHasKey( 'wp_slugs', $approved );
		$this->assertArrayHasKey( 'nfd_slugs', $approved );
		$this->assertContains( 'jetpack', $approved['wp_slugs'] );
		$this->assertContains( 'woocommerce', $approved['wp_slugs'] );
	}

	/**
	 * Get_domains includes approved domains.
	 *
	 * @return void
	 */
	public function test_get_domains_includes_wordpress_org() {
		$domains = Plugins::get_domains();
		$this->assertArrayHasKey( 'downloads.wordpress.org', $domains );
		$this->assertTrue( $domains['downloads.wordpress.org'] );
	}

	/**
	 * Wc_prevent_redirect_on_activation deletes WooCommerce activation redirect transient.
	 *
	 * @return void
	 */
	public function test_wc_prevent_redirect_on_activation_deletes_transient() {
		set_transient( '_wc_activation_redirect', 'yes', 60 );
		Plugins::wc_prevent_redirect_on_activation();
		$this->assertFalse( get_transient( '_wc_activation_redirect' ) );
	}
}
