<?php

namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Tasks\ThemeInstallTask;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;

/**
 * Manages the execution of ThemeInstallTasks.
 */
class ThemeInstallTaskManager extends AbstractTaskManager {

	/**
	 * Name of the ThemeInstallTaskManager Queue.
	 *
	 * @var string
	 */
	protected static $queue_name = 'theme_install_queue';

	/**
	 * Name of the ThemeInstallTaskManager Hook.
	 *
	 * @var string
	 */
	protected static $hook_name = 'nfd_module_installer_theme_install_cron';

	/**
	 * ThemeInstallTaskManager constructor.
	 */
	public function __construct() {
		parent::__construct();

		// Ten second cron hook
		add_action( self::$hook_name, array( $this, 'install' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( self::$hook_name ) ) {
			wp_schedule_event( time(), 'ten_seconds', self::$hook_name );
		}
	}

	/**
	 * Expedites an existing ThemeInstallTask with a given slug.
	 *
	 * @param string $theme_slug The theme slug to expedite.
	 * @return boolean
	 */
	public static function expedite( $theme_slug ) {
		$themes            = \get_option( Options::get_option_name( self::$queue_name ), array() );
		$position_in_queue = array_search( $theme_slug, array_column( $themes, 'slug' ), true );
		if ( false === $position_in_queue ) {
			return false;
		}

		$theme_to_install = $themes[ $position_in_queue ];
		unset( $themes[ $position_in_queue ] );
		$themes = array_values( $themes );
		\update_option( Options::get_option_name( self::$queue_name ), $themes );

		$theme_install_task = new ThemeInstallTask(
			$theme_to_install['slug'],
			$theme_to_install['activate'],
			$theme_to_install['priority'],
			$theme_to_install['retries']
		);

		// Update status to the current slug being installed.
		\update_option( Options::get_option_name( 'theme_init_status' ), $theme_install_task->get_slug() );

		// Execute the ThemeInstallTask.
		$status = $theme_install_task->execute();
		if ( \is_wp_error( $status ) ) {

			// If there is an error, then increase the retry count for the task.
			$theme_install_task->increment_retries();

			/*
				If the number of retries have not exceeded the limit
				then re-queue the task at the end of the queue to be retried.
			*/
			if ( $theme_install_task->get_retries() <= self::$retry_limit ) {
				array_push( $themes, $theme_install_task->to_array() );
			}
		}

		// If there are no more themes to be installed then change the status to completed.
		if ( empty( $themes ) ) {
			\update_option( Options::get_option_name( 'theme_init_status' ), 'completed' );
		}
		// Update the theme install queue.
		\update_option( Options::get_option_name( self::$queue_name ), $themes );

		return true;
	}

	/**
	 * Queue out a ThemeInstallTask with the highest priority in the theme install queue and execute it.
	 *
	 * @return array|false
	 */
	public function install() {
		/*
		Get the theme install tasks queued up to be installed, the ThemeInstallTask gets
		converted to an associative array before storing it in the option.
		*/
		$themes = \get_option( Options::get_option_name( self::$queue_name ), array() );

		/*
		Conversion of the max heap to an array will always place the ThemeInstallTask with the highest
		priority at the beginning of the array
		*/
		$theme_to_install = array_shift( $themes );
		if ( ! $theme_to_install ) {
			self::complete();
			return true;
		}

		// Recreate the ThemeInstallTask from the associative array.
		$theme_install_task = new ThemeInstallTask(
			$theme_to_install['slug'],
			$theme_to_install['activate'],
			$theme_to_install['priority'],
			$theme_to_install['retries']
		);

		// Update status to the current slug being installed.
		\update_option( Options::get_option_name( 'theme_init_status' ), $theme_install_task->get_slug() );

		// Execute the ThemeInstallTask.
		$status = $theme_install_task->execute();
		if ( \is_wp_error( $status ) ) {

			// If there is an error, then increase the retry count for the task.
			$theme_install_task->increment_retries();

			/*
				If the number of retries have not exceeded the limit
				then re-queue the task at the end of the queue to be retried.
			*/
			if ( $theme_install_task->get_retries() <= self::$retry_limit ) {
				array_push( $themes, $theme_install_task->to_array() );
			}
		}

		// If there are no more themes to be installed then change the status to completed.
		if ( empty( $themes ) ) {
			self::complete();
		}

		// Update the theme install queue.
		return \update_option( Options::get_option_name( self::$queue_name ), $themes );
	}

	/**
	 * Adds a new ThemeInstallTask to the Theme Install queue.
	 * The Task will be inserted at an appropriate position in the queue based on it's priority.
	 *
	 * @param ThemeInstallTask $theme_install_task Theme Install Task to add to the queue
	 * @return array|false
	 */
	public static function add_to_queue( ThemeInstallTask $theme_install_task ) {
		/*
		Get the ThemeInstallTasks queued up to be installed, the ThemeInstallTask gets
		converted to an associative array before storing it in the option.
		*/
		$themes = \get_option( Options::get_option_name( self::$queue_name ), array() );

		$queue = new PriorityQueue();
		foreach ( $themes as $queued_theme ) {
			/*
			Check if there is an already existing ThemeInstallTask in the queue
			for a given slug and activation criteria.
			*/
			if ( $queued_theme['slug'] === $theme_install_task->get_slug()
				&& $queued_theme['activate'] === $theme_install_task->get_activate() ) {
				return false;
			}
			$queue->insert( $queued_theme, $queued_theme['priority'] );
		}

		// Insert a new ThemeInstallTask at the appropriate position in the queue.
		$queue->insert(
			$theme_install_task->to_array(),
			$theme_install_task->get_priority()
		);

		return \update_option( Options::get_option_name( self::$queue_name ), $queue->to_array() );
	}


	/**
	 * Clear all the hook scheduling and update the status option
	 *
	 * @return bool
	 */
	private static function complete() {
		wp_clear_scheduled_hook( self::get_hook_name() );
		return \update_option( Options::get_option_name( 'theme_init_status' ), 'completed' );
	}
}
