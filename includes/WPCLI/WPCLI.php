<?php

namespace NewfoldLabs\WP\Module\Installer\WPCLI;

use NewfoldLabs\WP\Module\Installer\WPCLI\Handlers\InstallerCommandHandler;
use WP_CLI;

/**
 * Class WPCLI
 *
 * Handles registering WP-CLI commands for the Installer module.
 */
class WPCLI {
	/**
	 * Constructor for WPCLI class.
	 */
	public function __construct() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'installer', InstallerCommandHandler::class );
		}
	}
}
