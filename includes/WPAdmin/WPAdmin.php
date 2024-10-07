<?php

namespace NewfoldLabs\WP\Module\Installer\WPAdmin;

use NewfoldLabs\WP\Module\PLS\WPAdmin\Listeners\DataAttrListener;

/**
 * Manages all the wp-admin related functionalities for the module.
 */
class WPAdmin {
	/**
	 * Constructor for the WPAdmin class.
	 */
	public function __construct() {
		new DataAttrListener();
	}
}
