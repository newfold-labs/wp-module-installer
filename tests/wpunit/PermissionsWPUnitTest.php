<?php

namespace NewfoldLabs\WP\Module\Installer;

/**
 * Permissions wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Installer\Permissions
 */
class PermissionsWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * ADMIN constant is manage_options.
	 *
	 * @return void
	 */
	public function test_admin_constant() {
		$this->assertSame( 'manage_options', Permissions::ADMIN );
	}

	/**
	 * Rest_is_authorized_admin returns false when user not logged in.
	 *
	 * @return void
	 */
	public function test_rest_is_authorized_admin_returns_false_when_not_logged_in() {
		wp_set_current_user( 0 );
		$this->assertFalse( Permissions::rest_is_authorized_admin() );
	}

	/**
	 * Rest_is_authorized_admin returns true when admin is logged in.
	 *
	 * @return void
	 */
	public function test_rest_is_authorized_admin_returns_true_for_admin() {
		$user_id = static::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( Permissions::rest_is_authorized_admin() );
	}
}
