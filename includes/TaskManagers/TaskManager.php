<?php
namespace NewfoldLabs\WP\Module\Installer\TaskManagers;

use NewfoldLabs\WP\Module\Installer\Data\Options;

final class TaskManager {

	 protected $task_managers = array(
		 'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\PluginInstallTaskManager',
		 'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\PluginUninstallTaskManager',
		 'NewfoldLabs\\WP\Module\\Installer\\TaskManagers\\ThemeInstallTaskManager',
	 );

	 function __construct() {
		 foreach ( $this->task_managers as $task_manager ) {
			 if ( ! empty( get_option( $task_manager::get_queue_name(), array() ) ) ) {
				  new $task_manager();
			 }
		 }
	 }
}
