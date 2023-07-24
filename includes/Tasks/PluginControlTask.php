<?php
namespace NewfoldLabs\WP\Module\Installer\Tasks;

use NewfoldLabs\WP\Module\Installer\Services\PluginController;

/**
 * Task for Controlling a Plugin activation/deactivation.
 */
class PluginControlTask extends Task {

	/**
	 * Plugin Slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Plugin Activation/deactivation boolean.
	 *
	 * @var boolean
	 */
	private $activation;

	/**
	 * Task Priority.
	 *
	 * @var int
	 */
	private $priority;

	/**
	 * Task Retries Count.
	 *
	 * @var int
	 */
	private $retries;

	/**
	 * PluginControlTask constructor
	 *
	 * @param string  $slug The slug for the Plugin. Ref: includes/Data/Plugins.php for the slugs.
	 * @param boolean $activation Activation/deactivation criteria for the plugin.
	 * @param int     $priority Priority of the task, higher the number higher the priority.
	 * @param int     $retries The number of times the Task has been retried
	 */
	public function __construct( $slug, $activation, $priority = 0, $retries = 0 ) {
		$this->slug       = $slug;
		$this->activation = $activation;
		$this->priority   = $priority;
		$this->retries    = $retries;
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
	 * Retrieves activation criteria.
	 *
	 * @return boolean
	 */
	public function get_activation() {
		return $this->activation;
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
		$this->retries++;
	}

	/**
	 * Controls the Plugin using the PluginControl Service.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function execute() {
		return PluginController::control( $this->get_slug(), $this->get_activation() );
	}

	/**
	 * Convert the PluginControlTask into an associative array.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'slug'       => $this->slug,
			'activation' => $this->activation,
			'priority'   => $this->priority,
			'retries'    => $this->retries,
		);
	}

}
