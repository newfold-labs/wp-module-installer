<?php
namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Tasks\ThemeInstallTask;
use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;

/**
 * Manages the execution of ThemeInstallTasks.
 */
class ThemeInstallTaskManager {

	/**
	 * The number of times a ThemeInstallTask can be retried.
	 *
	 * @var int
	 */
	private static $retry_limit = 1;

	/**
	 * Name of the ThemeInstallTaskManager Queue.
	 *
	 * @var string
	 */
	private static $queue_name = 'theme_install_queue';

	/**
	 * ThemeInstallTaskManager constructor.
	 */
	public function __construct() {
		// Ensure there is a ten second option in the cron schedules
		add_filter( 'cron_schedules', array( $this, 'add_ten_seconds_schedule' ) );

		// Ten second cron hook
		add_action( 'nfd_module_installer_theme_install_cron', array( $this, 'install' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_module_installer_theme_install_cron' ) ) {
			wp_schedule_event( time(), 'ten_seconds', 'nfd_module_installer_theme_install_cron' );
		}
	}

	/**
	 * Retrieve the Queue Name for the TaskManager to perform Theme installation.
	 *
	 * @return string
	 */
	public static function get_queue_name() {
		return self::$queue_name;
	}

	/**
	 * Adds ten seconds option in the cron schedule.
	 *
	 * @param array $schedules Cron Schedule duration
	 * @return array
	 */
	public function add_ten_seconds_schedule( $schedules ) {
		if ( ! array_key_exists( 'ten_seconds', $schedules ) || 10 !== $schedules['ten_seconds']['interval'] ) {
			$schedules['ten_seconds'] = array(
				'interval' => 10,
				'display'  => __( 'Once Every Ten Seconds' ),
			);
		}

		return $schedules;
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
			\update_option( Options::get_option_name( 'theme_init_status' ), 'completed' );
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
	 * Returns the status of given plugin slug - installing/completed.
	 *
	 * @param string $theme Theme Slug
	 * @return string|false
	 */
	public static function status( $theme ) {
		$themes = \get_option( Options::get_option_name( self::$queue_name ), array() );
		return array_search( $theme, array_column( $themes, 'slug' ), true );
	}
}
