<?php
namespace NewfoldLabs\WP\Module\Installer\Tasks;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;

/**
 * Task for Deactivating a Plugin.
 */
class PluginDeactivationTask extends Task {

	/**
	 * Plugin Slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Task Priority.
	 *
	 * @var int
	 */
	private $priority;

	/**
	 * Task Deactivation Retries Count.
	 *
	 * @var int
	 */
	private $retries;

	/**
	 * PluginDeactivationTask constructor
	 *
	 * @param string $slug The slug for the Plugin. Ref: includes/Data/Plugins.php for the slugs.
	 * @param int    $priority Priority of the task, higher the number higher the priority.
	 * @param int    $retries The number of times the Task has been retried
	 */
	public function __construct( $slug, $priority = 0, $retries = 0 ) {
		$this->slug     = $slug;
		$this->priority = $priority;
		$this->retries  = $retries;
	}

	/**
	 * Retrieves Slug for the Plugin.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Retrieves Task Priority.
	 *
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Retrieves Task Deactivation retry count.
	 *
	 * @return string
	 */
	public function get_retries() {
		return $this->retries;
	}

	/**
	 * Increments retry count.
	 *
	 * @return void
	 */
	public function increment_retries() {
		++$this->retries;
	}

	/**
	 * Deactivates the Plugin using the PluginInstaller Service.
	 *
	 * @return \WP_REST_Response|WP_Error
	 */
	public function execute() {
		return PluginInstaller::deactivate( $this->get_slug() );
	}

	/**
	 * Convert the PluginDeactivationTask into an associative array.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'slug'     => $this->slug,
			'priority' => $this->priority,
			'retries'  => $this->retries,
		);
	}
}
