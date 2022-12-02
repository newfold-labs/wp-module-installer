<?php

use NewfoldLabs\WP\Module\Installer\Installer;
use NewfoldLabs\WP\ModuleLoader\Container;
use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				[
					'name'     => 'installer',
					'label'    => __( 'Installer', 'newfold-installer-module' ),
					'callback' => function ( Container $container ) {
						new Installer( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				]
			);

		}
	);

}
