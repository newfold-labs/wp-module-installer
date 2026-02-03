<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\Data\Options;

/**
 * Data Options wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Installer\Data\Options
 */
class DataOptionsWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Get_option_name returns prefixed name for valid key when attach_prefix true.
	 *
	 * @return void
	 */
	public function test_get_option_name_with_prefix() {
		$name = Options::get_option_name( 'plugins_init_status', true );
		$this->assertSame( 'nfd_module_installer_plugins_init_status', $name );
	}

	/**
	 * Get_option_name returns unprefixed name for valid key when attach_prefix false.
	 *
	 * @return void
	 */
	public function test_get_option_name_without_prefix() {
		$name = Options::get_option_name( 'plugin_install_queue', false );
		$this->assertSame( 'plugin_install_queue', $name );
	}

	/**
	 * Get_option_name returns false for invalid key.
	 *
	 * @return void
	 */
	public function test_get_option_name_returns_false_for_invalid_key() {
		$this->assertFalse( Options::get_option_name( 'nonexistent_key', true ) );
	}

	/**
	 * Get_all_options returns array with expected keys.
	 *
	 * @return void
	 */
	public function test_get_all_options_returns_expected_keys() {
		$options = Options::get_all_options();
		$this->assertArrayHasKey( 'plugins_init_status', $options );
		$this->assertArrayHasKey( 'plugin_install_queue', $options );
		$this->assertArrayHasKey( 'theme_install_queue', $options );
	}
}
