<?php
namespace NewfoldLabs\WP\Module\Installer\RestApi;

use NewfoldLabs\WP\Module\Installer\Data\Options;
use NewfoldLabs\WP\Module\Installer\Permissions;
use NewfoldLabs\WP\Module\Installer\Data\Plugins;
use NewfoldLabs\WP\Module\Installer\Services\PluginInstaller;
use NewfoldLabs\WP\Module\Installer\Services\PluginUninstaller;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginInstallTask;
use NewfoldLabs\WP\Module\Installer\TaskManagers\PluginInstallTaskManager;
use NewfoldLabs\WP\Module\Installer\Tasks\PluginUninstallTask;
use NewfoldLabs\WP\Module\Installer\TaskManagers\PluginUninstallTaskManager;

/**
 * Class PluginsController
 */
class PluginsController {
	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'newfold-installer/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base = '/plugins';

	/**
	 * Registers rest routes for PluginsController class.
	 *
	 * @return void
	 */
	public function register_routes() {
		\register_rest_route(
			$this->namespace,
			$this->rest_base . '/approved',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_approved_plugins' ),
					'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				),
			)
		);

		\register_rest_route(
			$this->namespace,
			$this->rest_base . '/install',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'install' ),
					'args'                => $this->get_install_plugin_args(),
					'permission_callback' => array( PluginInstaller::class, 'check_install_permissions' ),
				),
			)
		);

		\register_rest_route(
			$this->namespace,
			$this->rest_base . '/uninstall',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'uninstall' ),
					'args'                => $this->get_install_plugin_args(),
					'permission_callback' => array( PluginInstaller::class, 'check_install_permissions' ),
				),
			)
		);

		\register_rest_route(
			$this->namespace,
			$this->rest_base . '/status',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_status' ),
					'args'                => $this->get_status_args(),
					'permission_callback' => array( Permissions::class, 'rest_is_authorized_admin' ),
				),
			)
		);
	}

	/**
	 * Get approved plugin slugs, urls and domains.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_approved_plugins() {

		return new \WP_REST_Response(
			Plugins::get_approved(),
			200
		);
	}

	/**
	 * Get args for the install route.
	 *
	 * @return array
	 */
	public function get_install_plugin_args() {
		return array(
			'plugin'   => array(
				'type'     => 'string',
				'required' => true,
			),
			'activate' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'queue'    => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'priority' => array(
				'type'    => 'integer',
				'default' => 0,
			),
			'premium'  => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'provider' => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}

	/**
	 * Get args for the uninstall route.
	 *
	 * @return array
	 */
	public function get_uninstall_plugin_args() {
		return array(
			'plugin'   => array(
				'type'     => 'string',
				'required' => true,
			),
			'queue'    => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'priority' => array(
				'type'    => 'integer',
				'default' => 0,
			),
		);
	}

	/**
	 * Get the plugin status check arguments.
	 *
	 * @return array
	 */
	public function get_status_args() {
		return array(
			'plugin'    => array(
				'type'     => 'string',
				'required' => true,
			),
			'activated' => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);
	}

	/**
	 * Install the requested plugin via a zip url (or) slug.
	 *
	 * @param \WP_REST_Request $request the incoming request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function install( \WP_REST_Request $request ) {
		$plugin   = $request->get_param( 'plugin' );
		$activate = $request->get_param( 'activate' );
		$queue    = $request->get_param( 'queue' );
		$priority = $request->get_param( 'priority' );
		$premium  = $request->get_param( 'premium' );
		$provider = $request->get_param( 'provider' );

		// Checks if the plugin is premium and uses the corresponding function for it.
		if ( true === $premium ) {
			return PluginInstaller::install_premium_plugin( $plugin, $provider, $activate );
		}

		// Checks if a plugin with the given slug and activation criteria already exists.
		if ( PluginInstaller::exists( $plugin, $activate ) ) {
			return new \WP_REST_Response(
				array(),
				200
			);
		}

		// Queue the plugin install if specified in the request.
		if ( $queue ) {
			// Add a new PluginInstallTask to the Plugin install queue.
			PluginInstallTaskManager::add_to_queue(
				new PluginInstallTask(
					$plugin,
					$activate,
					$priority
				)
			);

			return new \WP_REST_Response(
				array(),
				202
			);
		}

		// Execute the task if it need not be queued.
		$plugin_install_task = new PluginInstallTask( $plugin, $activate );

		return $plugin_install_task->execute();
	}

	/**
	 * Handle an uninstall requuest.
	 *
	 * @param \WP_REST_Request $request The incoming request object.
	 * @return \WP_REST_Response
	 */
	public function uninstall( \WP_REST_Request $request ) {
		$plugin   = $request->get_param( 'plugin' );
		$queue    = $request->get_param( 'queue' );
		$priority = $request->get_param( 'priority' );

		$position_in_queue = PluginInstallTaskManager::status( $plugin );
		if ( false !== $position_in_queue && 0 !== $position_in_queue ) {
			PluginInstallTaskManager::remove_from_queue(
				$plugin
			);

			return new \WP_REST_Response(
				array(),
				200
			);
		}

		if ( ! PluginUninstaller::exists( $plugin ) ) {
			return new \WP_REST_Response(
				array(),
				200
			);
		}

		// Queue the plugin uninstall if specified in the request.
		if ( $queue ) {
			// Add a new PluginUninstallTask to the Plugin install queue.
			PluginUninstallTaskManager::add_to_queue(
				new PluginUninstallTask(
					$plugin,
					$priority
				)
			);

			return new \WP_REST_Response(
				array(),
				202
			);
		}

		// Execute the task if it need not be queued.
		$plugin_uninstall_task = new PluginUninstallTask( $plugin );

		return $plugin_uninstall_task->execute();
	}

	/**
	 * Returns the status of a given plugin slug.
	 *
	 * @param \WP_REST_Request $request the incoming request object.
	 * @return \WP_REST_Response
	 */
	public function get_status( \WP_REST_Request $request ) {
		$plugin    = $request->get_param( 'plugin' );
		$activated = $request->get_param( 'activated' );

		if ( PluginInstaller::exists( $plugin, $activated ) ) {
			return new \WP_REST_Response(
				array(
					'status' => $activated ? 'activated' : 'installed',
				),
				200
			);
		}

		$position_in_queue = PluginInstallTaskManager::status( $plugin );

		if ( false !== $position_in_queue ) {
			return new \WP_REST_Response(
				array(
					'status'   => 'installing',
					'estimate' => ( ( $position_in_queue + 1 ) * 30 ),
				),
				200
			);
		}

		$in_progress_plugin = \get_option( Options::get_option_name( 'plugins_init_status' ), '' );
		if ( $in_progress_plugin === $plugin ) {
			return new \WP_REST_Response(
				array(
					'status'   => 'installing',
					'estimate' => 30,
				),
				200
			);
		}

		return new \WP_REST_Response(
			array(
				'status' => 'inactive',
			),
			200
		);
	}
}
