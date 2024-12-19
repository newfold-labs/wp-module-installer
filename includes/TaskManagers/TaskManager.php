<?php

namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;

/**
 * Class TaskManager
 */
final class TaskManager {

	/**
	 * List of Task Managers.
	 *
	 * @var array
	 */
	protected $task_managers = array(
		'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\PluginInstallTaskManager',
		'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\PluginUninstallTaskManager',
		'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\ThemeInstallTaskManager',
		'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\PluginActivationTaskManager',
		'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\PluginDeactivationTaskManager',
	);

	/**
	 * Constructor that registers all the TaskManagers.
	 */
	public function __construct() {
		TaskManagerSchedules::init();

		/**
		 * Task Manager Class
		 *
		 * @var $task_manager PluginUninstallTaskManager|PluginDeactivationTaskManager|ThemeInstallTaskManager|PluginInstallTaskManager|PluginActivationTaskManager
		 */
		foreach ( $this->task_managers as $task_manager ) {
			if ( wp_next_scheduled( $task_manager::get_hook_name() ) || ! empty( get_option( Options::get_option_name( $task_manager::get_queue_name() ), array() ) ) ) {
				new $task_manager();
			}
		}
	}
}
