<?php
namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginInstallTask;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;

/**
 * Manages the execution of PluginInstallTasks.
 */
class PluginInstallTaskManager {

	/**
	 * The number of times a PluginInstallTask can be retried.
	 *
	 * @var int
	 */
	private static $retry_limit = 1;

	/**
	 * The name of the queue, might be prefixed.
	 *
	 * @var string
	 */
	private static $queue_name = 'plugin_install_queue';

	/**
	 * Schedules the crons.
	 */
	public function __construct() {
		// Ensure there is a thirty second option in the cron schedules
		add_filter( 'cron_schedules', array( $this, 'add_thirty_seconds_schedule' ) );

		// Thirty second cron hook
		add_action( 'nfd_module_installer_plugin_install_cron', array( $this, 'install' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_module_installer_plugin_install_cron' ) ) {
			wp_schedule_event( time(), 'thirty_seconds', 'nfd_module_installer_plugin_install_cron' );
		}
	}

	/**
	 * Returns the queue name, might be prefixed.
	 *
	 * @return string
	 */
	public static function get_queue_name() {
		return self::$queue_name;
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
				'display'  => __( 'Once Every Thirty Seconds' ),
			);
		}

		return $schedules;
	}

	/**
	 * Queue out a PluginInstallTask with the highest priority in the plugin install queue and execute it.
	 *
	 * @return array|false
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
			return true;
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

		// If there are no more plugins to be installed then change the status to completed.
		if ( empty( $plugins ) ) {
			return \update_option( Options::get_option_name( 'plugins_init_status' ), 'completed' );
		}

		return true;
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
			for a given slug and activation criteria.
			*/
			if ( $queued_plugin['slug'] === $plugin_install_task->get_slug()
				&& $queued_plugin['activate'] === $plugin_install_task->get_activate() ) {
				return false;
			}
			$queue->insert( $queued_plugin, $queued_plugin['priority'] );
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
	 * Get the status of a given plugin slug from the queue.
	 *
	 * @param string $plugin The slug of the plugin.
	 * @return boolean
	 */
	public static function status( $plugin ) {
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );
		return array_search( $plugin, array_column( $plugins, 'slug' ), true );
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

	/**
	 * Gets the list of Plugins in the activation queue and requeues them with activation true
	 *
	 * @return bool
	 */
	public static function requeue_with_changed_activation( ) {
		/*
		Get the plugins queued up to be installed.
		*/
		$plugins = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$queue = new PriorityQueue();
		foreach ( $plugins as $queued_plugin ) {
			/*
			Get a plugin task that is in the queue and insert it at the end with changed activation criteria
			*/
			if ( false === $queued_plugin['activate'] ) {
				$queued_plugin['activate'] = true;
			}
			$queue->insert( $queued_plugin, $queued_plugin['priority'] );
		}

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}
}
