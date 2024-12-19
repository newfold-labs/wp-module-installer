<?php

namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;

/**
 * Manages the execution of Tasks.
 */
abstract class AbstractTaskManager {

	/**
	 * The number of times a PluginUninstallTask can be retried.
	 *
	 * @var int
	 */
	protected static $retry_limit = 1;

	/**
	 * Name of the Queue.
	 *
	 * @var string
	 */
	protected static $queue_name = '';

	/**
	 * Name of the Hook.
	 *
	 * @var string
	 */
	protected static $hook_name = '';

	/**
	 * PluginUninstallTaskManager constructor.
	 */
	public function __construct() {
		TaskManagerSchedules::init();
	}

	/**
	 * Retrieve the Queue Name for the TaskManager.
	 *
	 * @return string
	 */
	public static function get_queue_name() {
		return static::$queue_name;
	}


	/**
	 * Retrieve the Hook Name for the TaskManager.
	 *
	 * @return string
	 */
	public static function get_hook_name() {
		return static::$hook_name;
	}


	/**
	 * Returns the status of given plugin slug - uninstalling/completed.
	 *
	 * @param string $plugin Plugin Slug
	 * @return string|false
	 */
	public static function status( $plugin ) {
		$plugins = \get_option( Options::get_option_name( static::$queue_name ), array() );
		return array_search( $plugin, array_column( $plugins, 'slug' ), true );
	}
}
