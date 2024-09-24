<?php
namespace NewfoldLabs\WP\Module\Installer\Tasks;

use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;

/**
 * Task for installing a Plugin.
 */
class PluginInstallTask extends Task {

	/**
	 * Plugin Slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Plugin Activation Status.
	 *
	 * @var boolean
	 */
	private $activate;

	/**
	 * Task Priority.
	 *
	 * @var int
	 */
	private $priority;

	/**
	 * Task Installation Retries Count.
	 *
	 * @var int
	 */
	private $retries;

	/**
	 * Boolean if the given plugin is a entitlement.
	 *
	 * @var int
	 */
	private $is_entitlement;

	/**
	 * Meta information about a plugin, useful for entitlements.
	 *
	 * @var int
	 */
	private $meta;

	/**
	 * PluginInstallTask constructor
	 *
	 * @param string  $slug The slug for the Plugin. Ref: includes/Data/Plugins.php for the slugs.
	 * @param boolean $activate A value of true activates the plugin.
	 * @param int     $priority Priority of the task, higher the number higher the priority.
	 * @param int     $retries The number of times the Task has been retried
	 * @param boolean $is_entitlement If the given plugin is a entitlement
	 * @param object  $meta Meta information about a plugin
	 */
	public function __construct( $slug, $activate, $priority = 0, $retries = 0, $is_entitlement = false, $meta = array() ) {
		$this->slug           = $slug;
		$this->activate       = $activate;
		$this->priority       = $priority;
		$this->retries        = $retries;
		$this->is_entitlement = $is_entitlement;
		$this->meta           = $meta;
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
	 * Retrieves Plugin Activation Status.
	 *
	 * @return boolean
	 */
	public function get_activate() {
		return $this->activate;
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
	 * Retrieves Task Installation retry count.
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
	 * Installs the Plugin using the PluginInstaller Service.
	 *
	 * @return \WP_REST_Response|WP_Error
	 */
	public function execute() {
		return PluginInstaller::install( $this->get_slug(), $this->get_activate() );
	}

	/**
	 * Convert the PluginInstallTask into an associative array.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'slug'     => $this->slug,
			'activate' => $this->activate,
			'priority' => $this->priority,
			'retries'  => $this->retries,
		);
	}
}
