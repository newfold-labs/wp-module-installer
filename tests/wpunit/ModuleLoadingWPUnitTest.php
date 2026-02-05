<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\Data\Constants;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;

/**
 * Module loading wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Installer\Installer
 */
class ModuleLoadingWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Verify core module classes exist.
	 *
	 * @return void
	 */
	public function test_module_classes_load() {
		$this->assertTrue( class_exists( Installer::class ) );
		$this->assertTrue( class_exists( Permissions::class ) );
		$this->assertTrue( class_exists( Constants::class ) );
		$this->assertTrue( class_exists( PriorityQueue::class ) );
	}

	/**
	 * Verify WordPress factory is available.
	 *
	 * @return void
	 */
	public function test_wordpress_factory_available() {
		$this->assertTrue( function_exists( 'get_option' ) );
		$this->assertNotEmpty( get_option( 'blogname' ) );
	}
}
