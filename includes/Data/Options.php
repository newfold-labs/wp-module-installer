<?php
namespace NewfoldLabs\WP\Module\Installer\Data;

/**
 * Stores all the WordPress Options used in the module.
 */
final class Options {
	 /**
	  * @var string Prefix for options in the module.
	  */
	protected static $prefix = 'nfd_module_installer_';

	 /**
	  * @var array List of all the options
	  */
	protected static $options = array(
		'plugins_init_status'    => 'plugins_init_status',
		'plugin_install_queue'   => 'plugin_install_queue',
		'plugin_uninstall_queue' => 'plugin_uninstall_queue',
		'theme_init_status'      => 'theme_init_status',
		'theme_install_queue'    => 'theme_install_queue',
	);

	 /**
	  * Get option name for a given key.
	  *
	  * @param string $option_key The key for the Options::$options array.
	  * @param bool   $attach_prefix Attach the module prefix.
	  *
	  * @return string
	  */
	public static function get_option_name( $option_key, $attach_prefix = true ) {
		 return isset( self::$options[ $option_key ] )
				   ? ( $attach_prefix
						? self::$prefix . self::$options[ $option_key ]
						: self::$options[ $option_key ]
					)
				   : false;
	}

	 /**
	  * @return array List of all options.
	  */
	public static function get_all_options() {
		 return self::$options;
	}
}
