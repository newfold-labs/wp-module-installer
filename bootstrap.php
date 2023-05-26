<?php

use NewfoldLabs\WP\Module\Installer\Installer;
use NewfoldLabs\WP\ModuleLoader\Container;
use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				array(
					'name'     => 'installer',
					'label'    => __( 'Installer', 'newfold-installer-module' ),
					'callback' => function ( Container $container ) {

						if ( ! defined( 'NFD_INSTALLER_VERSION' ) ) {
							define( 'NFD_INSTALLER_VERSION', '0.0.1' );
						}

						new Installer( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);

		}
	);

}
