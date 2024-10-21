<?php

namespace NewfoldLabs\WP\Module\Installer\WPAdmin;

use NewfoldLabs\WP\Module\Installer\WPAdmin\Listeners\InstallerListener;

/**
 * Manages all the wp-admin related functionalities for the module.
 */
class WPAdmin {
	/**
	 * Constructor for the WPAdmin class.
	 */
	public function __construct() {
		new InstallerListener();
	}
}
