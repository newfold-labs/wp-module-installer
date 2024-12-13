<?php

namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

/**
 * Class TaskManagerSchedules
 */
abstract class TaskManagerSchedules {

	/**
	 * List of Task Managers.
	 *
	 * @var array
	 */
	protected static $schedules = array();

	/**
	 * Init the Task Manager Schedules class
	 */
	public static function init() {
		static $initialized = false;

		if ( ! $initialized ) {
			self::init_schedules();
			add_filter( 'cron_schedules', array( __CLASS__, 'add_schedules' ) );
		}
	}

	protected static function init_schedules() {
		self::$schedules = array(
			'thirty_seconds' => array(
				'interval' => 30,
				'display'  => __( 'Once Every Thirty Seconds' ),
			),
			'ten_seconds'    => array(
				'interval' => 10,
				'display'  => __( 'Once Every Ten Seconds' ),
			),
		);
	}

	/**
	 * Adds a task manager cron schedule.
	 *
	 * @param array $schedules The existing cron schedule.
	 * @return array
	 */
	public static function add_schedules( $schedules ) {
		foreach ( self::$schedules as $schedule_slug => $schedule_data ) {
			if ( ! array_key_exists( $schedule_slug, $schedules ) || $schedule_data[ 'interval' ] !== $schedules[ $schedule_slug ][ 'interval' ] ) {
				$schedules[ $schedule_slug ] = $schedule_data;
			}
		}

		return $schedules;
	}
}
