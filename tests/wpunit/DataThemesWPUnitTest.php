<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\Data\Themes;

/**
 * Data Themes wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Installer\Data\Themes
 */
class DataThemesWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Get returns array with nfd_slugs.
	 *
	 * @return void
	 */
	public function test_get_returns_nfd_slugs() {
		$data = Themes::get();
		$this->assertArrayHasKey( 'nfd_slugs', $data );
		$this->assertIsArray( $data['nfd_slugs'] );
	}

	/**
	 * Get_approved returns approved theme nfd_slugs.
	 *
	 * @return void
	 */
	public function test_get_approved_returns_approved_slugs() {
		$approved = Themes::get_approved();
		$this->assertArrayHasKey( 'nfd_slugs', $approved );
		$this->assertContains( 'nfd_slug_yith_wonder', $approved['nfd_slugs'] );
		$this->assertContains( 'nfd_slug_bluehost_blueprint', $approved['nfd_slugs'] );
	}
}
