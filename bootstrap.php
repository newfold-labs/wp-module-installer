<?php

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Installer\Installer;
use NewfoldLabs\WP\Module\Installer\Data\Constants;

use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				array(
					'name'     => 'installer',
					'label'    => __( 'Installer', 'wp-module-installer' ),
					'callback' => function ( Container $container ) {
						new Constants( $container );
						new Installer( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);
		}
	);

}
