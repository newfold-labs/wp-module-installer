<?php

namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginInstallTask;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;

/**
 * Manages the execution of PluginInstallTasks.
 */
class PluginInstallTaskManager extends AbstractTaskManager {

	/**
	 * The name of the queue, might be prefixed.
	 *
	 * @var string
	 */
	protected static $queue_name = 'plugin_install_queue';

	/**
	 * The name of the Hook.
	 *
	 * @var string
	 */
	protected static $hook_name = 'nfd_module_installer_plugin_install_cron';

	/**
	 * Schedules the crons.
	 */
	public function __construct() {
		parent::__construct();

		// Thirty seconds cron hook
		add_action( self::$hook_name, array( $this, 'install' ) );

		if ( ! wp_next_scheduled( self::$hook_name ) ) {
			wp_schedule_event( time(), 'thirty_seconds', self::$hook_name );
		}
	}


	/**
	 * Adds a 30 second cron schedule.
	 *
	 * @param array $schedules The existing cron schedule.
	 * @return array
	 */
	public function add_thirty_seconds_schedule( $schedules ) {
		if ( ! array_key_exists( 'thirty_seconds', $schedules ) || 30 !== $schedules['thirty_seconds']['interval'] ) {
			$schedules['thirty_seconds'] = array(
				'interval' => 30,
				'display'  => __( 'Once Every Thirty Seconds', 'wp-module-installer' ),
			);
		}

		return $schedules;
	}

	/**
	 * Queue out a PluginInstallTask with the highest priority in the plugin install queue and execute it.
	 *
	 * @void
	 */
	public function install() {
		/*
		Get the plugins queued up to be installed, the PluginInstall task gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		/*
		Conversion of the max heap to an array will always place the PluginInstallTask with the highest
		priority at the beginning of the array
		*/
		$plugin_to_install = array_shift( $plugins );

		if ( ! $plugin_to_install ) {
			self::complete();

			return;
		}

		// Update the plugin install queue.
		\update_option( Options::get_option_name( self::$queue_name ), $plugins );

		// Recreate the PluginInstall task from the associative array.
		$plugin_install_task = new PluginInstallTask(
			$plugin_to_install['slug'],
			$plugin_to_install['activate'],
			$plugin_to_install['priority'],
			$plugin_to_install['retries']
		);

		// Update status to the current slug being installed.
		\update_option( Options::get_option_name( 'plugins_init_status' ), $plugin_install_task->get_slug() );

		// Execute the PluginInstall Task.
		$status = $plugin_install_task->execute();
		if ( \is_wp_error( $status ) ) {

			// If there is an error, then increase the retry count for the task.
			$plugin_install_task->increment_retries();

			// Get Latest Value of the install queue
			$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

			/*
			If the number of retries have not exceeded the limit
			then re-queue the task at the end of the queue to be retried.
			*/
			if ( $plugin_install_task->get_retries() <= self::$retry_limit ) {
				array_push( $plugins, $plugin_install_task->to_array() );

				// Update the plugin install queue.
				\update_option( Options::get_option_name( self::$queue_name ), $plugins );
			}
		}

		// If there are no more plugins to be installed then change the status to complete.
		if ( empty( $plugins ) ) {
			self::complete();
		}
	}

	/**
	 * Adds a new PluginInstallTask to the Plugin Install queue.
	 * The Task will be inserted at an appropriate position in the queue based on it's priority.
	 *
	 * @param PluginInstallTask $plugin_install_task The task to be inserted.
	 * @return array|false
	 */
	public static function add_to_queue( PluginInstallTask $plugin_install_task ) {
		/*
		Get the plugins queued up to be installed, the PluginInstall task gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$queue = new PriorityQueue();
		foreach ( $plugins as $queued_plugin ) {
			/*
			Check if there is an already existing PluginInstallTask in the queue
			for a given slug.
			*/
			if ( $queued_plugin['slug'] !== $plugin_install_task->get_slug() ) {
				$queue->insert( $queued_plugin, $queued_plugin['priority'] );
			}
		}

		// Insert a new PluginInstallTask at the appropriate position in the queue.
		$queue->insert(
			$plugin_install_task->to_array(),
			$plugin_install_task->get_priority()
		);

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}

	/**
	 * Removes a PluginInstallTask from the queue.
	 *
	 * @param string $plugin The slug of the task to remove.
	 * @return array
	 */
	public static function remove_from_queue( $plugin ) {
		/*
		Get the plugins queued up to be installed, the PluginInstall task gets
		converted to an associative array before storing it in the option.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$queue = new PriorityQueue();
		foreach ( $plugins as $queued_plugin ) {
			/*
			If the Plugin slug does not match add it back to the queue.
			*/
			if ( $queued_plugin['slug'] !== $plugin ) {
				$queue->insert( $queued_plugin, $queued_plugin['priority'] );
			}
		}

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}


	/**
	 * Clear all the hook scheduling and update the status option
	 *
	 * @return bool
	 */
	private static function complete() {
		wp_clear_scheduled_hook( 'nfd_module_installer_plugin_install_cron' );
		return \update_option( Options::get_option_name( 'plugins_init_status' ), 'completed' );
	}

	/**
	 * Reset the Plugin install status and the queue.
	 *
	 * @return void
	 */
	public static function reset_install_status() {
		\delete_option( Options::get_option_name( 'plugins_init_status' ) );
		\delete_option( Options::get_option_name( 'plugin_install_queue' ) );
	}
}
