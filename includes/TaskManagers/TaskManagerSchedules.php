<?php

namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

/**
 * Class TaskManagerSchedules
 */
abstract class TaskManagerSchedules {

	/**
	 * Init the Task Manager Schedules class
	 */
	public static function init() {
		static $initialized = false;

		if ( ! $initialized ) {
			add_filter( 'cron_schedules', array( __CLASS__, 'add_schedules' ) );
		}
	}

	/**
	 * Adds a task manager cron schedule.
	 *
	 * @param array $schedules The existing cron schedule.
	 * @return array
	 */
	public static function add_schedules( $schedules ) {
		$schedules_to_add = array(
			'thirty_seconds' => array(
				'interval' => 30,
				'display'  => __( 'Once Every Thirty Seconds', 'wp-module-installer' ),
			),
			'ten_seconds'    => array(
				'interval' => 10,
				'display'  => __( 'Once Every Ten Seconds', 'wp-module-installer' ),
			),
		);

		foreach ( $schedules_to_add as $schedule_slug => $schedule_data ) {
			if ( ! array_key_exists( $schedule_slug, $schedules ) || $schedule_data['interval'] !== $schedules[ $schedule_slug ]['interval'] ) {
				$schedules[ $schedule_slug ] = $schedule_data;
			}
		}

		return $schedules;
	}
}
