<?php
namespace NewfoldLabs\WP\Module\Installer\Services;

use NewfoldLabs\WP\Module\Installer\Data\Plugins;
use NewfoldLabs\WP\Module\Installer\Permissions;
use NewfoldLabs\WP\Module\PLS\Utilities\PLSUtility;

/**
 * Class PluginInstaller
 */
class PluginInstaller {

	/**
	 * Install a whitelisted plugin.
	 *
	 * @param string  $plugin The plugin slug from Plugins.php.
	 * @param boolean $should_activate Whether to activate the plugin after install.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public static function install( $plugin, $should_activate = true ) {
		$plugins_list = Plugins::get();

		// Check if the plugin param contains a zip url.
		if ( \wp_http_validate_url( $plugin ) ) {
			$domain = \wp_parse_url( $plugin, PHP_URL_HOST );
			// If the zip URL/domain is not approved.
			if ( ! isset( $plugins_list['urls'][ $plugin ] )
				&& ! isset( $plugins_list['domains'][ $domain ] ) ) {
					return new \WP_Error(
						'plugin-error',
						/* Translators: %s plugin slug */
						sprintf( __( 'You do not have permission to install from %s.', 'wp-module-installer' ), $plugin ),
						array( 'status' => 400 )
					);
			}

			$status = self::install_from_zip( $plugin, $should_activate );
			if ( \is_wp_error( $status ) ) {
				return $status;
			}

			return new \WP_REST_Response(
				array(),
				201
			);
		}

		// If it is not a zip URL then check if it is an approved slug.
		$plugin = \sanitize_text_field( $plugin );
		if ( self::is_nfd_slug( $plugin ) ) {
			// [TODO] Better handle mu-plugins and direct file downloads.
			if ( 'nfd_slug_endurance_page_cache' === $plugin ) {
				return self::install_endurance_page_cache();
			}
			$plugin_path = $plugins_list['nfd_slugs'][ $plugin ]['path'];
			if ( ! self::is_plugin_installed( $plugin_path ) ) {
				$status = self::install_from_zip( $plugins_list['nfd_slugs'][ $plugin ]['url'], $should_activate );
				if ( \is_wp_error( $status ) ) {
					return $status;
				}
			}
			if ( $should_activate && ! \is_plugin_active( $plugin_path ) ) {
				$status = \activate_plugin( $plugin_path );
				if ( \is_wp_error( $status ) ) {
					$status->add_data( array( 'status' => 500 ) );

					return $status;
				}
			}
			return new \WP_REST_Response(
				array(),
				201
			);
		}

		if ( ! isset( $plugins_list['wp_slugs'][ $plugin ] ) ) {
			return new \WP_Error(
				'plugin-error',
				/* Translators: %s plugin slug */
				sprintf( __( 'You do not have permission to install %s.', 'wp-module-installer' ), $plugin ),
				array( 'status' => 400 )
			);
		}

		$plugin_path                  = $plugins_list['wp_slugs'][ $plugin ]['path'];
		$plugin_post_install_callback = isset( $plugins_list['wp_slugs'][ $plugin ]['post_install_callback'] )
		? $plugins_list['wp_slugs'][ $plugin ]['post_install_callback']
		: false;
		if ( ! self::is_plugin_installed( $plugin_path ) ) {
			$status = self::install_from_wordpress( $plugin, $should_activate );
			if ( \is_wp_error( $status ) ) {
				return $status;
			}
			if ( is_callable( $plugin_post_install_callback ) ) {
				$plugin_post_install_callback();
			}
		}

		if ( $should_activate && ! \is_plugin_active( $plugin_path ) ) {
			$status = \activate_plugin( $plugin_path );
			if ( \is_wp_error( $status ) ) {
				$status->add_data( array( 'status' => 500 ) );

				return $status;
			}
		}

		return new \WP_REST_Response(
			array(),
			201
		);
	}

	/**
	 * Install a plugin from wordpress.org.
	 *
	 * @param string  $plugin The wp_slug to install.
	 * @param boolean $should_activate Whether to activate the plugin after install.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function install_from_wordpress( $plugin, $should_activate = true ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = \plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin,
				'fields' => array(
					'sections'       => false,
					'language_packs' => true,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			if ( false !== strpos( $api->get_error_message(), 'Plugin not found.' ) ) {
				$api->add_data( array( 'status' => 404 ) );
			} else {
				$api->add_data( array( 'status' => 500 ) );
			}

			return $api;
		}

		$status = self::install_from_zip( $api->download_link, $should_activate, $api->language_packs );
		if ( \is_wp_error( $status ) ) {
			return $status;
		}

		return new \WP_REST_Response(
			array(),
			200
		);
	}

	/**
	 * Provisions a license and installs or activates a premium plugin.
	 *
	 * @param string  $plugin The slug of the premium plugin.
	 * @param string  $provider The provider name for the premium plugin.
	 * @param boolean $should_activate Whether to activate the plugin after installation. (default: true)
	 * @param mixed   $plugin_basename The plugin basename, if known. (default: false)
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public static function install_premium_plugin( $plugin, $provider, $should_activate = true, $plugin_basename = false ) {
		$is_installed = false;
		$is_active    = false;

		// Ensure plugin and provider are not empty
		if ( empty( $plugin ) || empty( $provider ) ) {
			return new \WP_Error(
				'nfd_installer_error',
				__( 'Plugin slug and provider name cannot be empty.', 'wp-module-installer' )
			);
		}

		$pls_utility = new PLSUtility();

		// Provision a license for the premium plugin, this returns basename and download URL
		$license_response = $pls_utility->provision_license( $plugin, $provider );
		if ( is_wp_error( $license_response ) ) {
			$license_response->add(
				'nfd_installer_error',
				__( 'Failed to provision license for premium plugin: ', 'wp-module-installer' ) . $plugin,
				array(
					'plugin'   => $plugin,
					'provider' => $provider,
				)
			);
			return $license_response;
		}

		// Maybe get the plugin basename from the license response
		// This is only returned if the plugin is already installed and licensed
		$plugin_basename = ! empty( $license_response['basename'] ) ? $license_response['basename'] : false;

		// Check if the plugin is already installed
		if ( $plugin_basename && self::is_plugin_installed( $plugin_basename ) ) {
			$is_installed = true;
		}
		// If NOT installed, install plugin
		if ( ! $is_installed ) {
			// Check if the download URL is present in the license response
			if ( empty( $license_response['downloadUrl'] ) ) {
				return new \WP_Error(
					'nfd_installer_error',
					__( 'Download URL is missing for premium plugin: ', 'wp-module-installer' ) . $plugin,
					array(
						'plugin'   => $plugin,
						'provider' => $provider,
					)
				);
			}
			$install_status = self::install_from_zip( $license_response['downloadUrl'], $should_activate );
			if ( is_wp_error( $install_status ) ) {
				$install_status->add(
					'nfd_installer_error',
					__( 'Failed to install or activate the premium plugin: ', 'wp-module-installer' ) . $plugin,
					array(
						'plugin'       => $plugin,
						'provider'     => $provider,
						'download_url' => $license_response['downloadUrl'],
					)
				);
				return $install_status;
			}
		}

		// Check if the plugin is already active
		// Can only be true if the plugin was already installed
		// Only need to check if it should be activated
		if ( $is_installed && $should_activate && is_plugin_active( $plugin_basename ) ) {
			$is_active = true;
		}
		// If should activate, and not already active, activate the plugin
		if ( $is_installed && $should_activate && ! $is_active ) {
			$activate_plugin_response = activate_plugin( $plugin_basename );
			if ( is_wp_error( $activate_plugin_response ) ) {
				$activate_plugin_response->add(
					'nfd_installer_error',
					__( 'Failed to activate the plugin: ', 'wp-module-installer' ) . $plugin,
					array(
						'plugin'   => $plugin,
						'provider' => $provider,
						'basename' => $plugin_basename,
					)
				);
				return $activate_plugin_response;
			}
		}

		// Activate the license
		// Should we do this here or let the activation hook handle it - see WPAdmin/Listeners/InstallerListener.php
		$license_activation_response = $pls_utility->activate_license( $plugin );
		if ( is_wp_error( $license_activation_response ) ) {
			$license_activation_response->add(
				'nfd_installer_error',
				__( 'Failed to activate the license for the premium plugin: ', 'wp-module-installer' ) . $plugin,
				array(
					'plugin'   => $plugin,
					'provider' => $provider,
				)
			);
			return $license_activation_response;
		}

		// Return success response
		return new \WP_REST_Response(
			array(
				'message' => __( 'Successfully provisioned and installed: ', 'wp-module-installer' ) . $plugin,
			),
			200
		);
	}

	/**
	 * Install the plugin from a custom ZIP.
	 *
	 * @param string  $url The ZIP URL to install from.
	 * @param boolean $should_activate Whether to activate the plugin after install.
	 * @param array   $language_packs The set of language packs to install for the plugin.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function install_from_zip( $url, $should_activate, $language_packs = array() ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		\wp_cache_flush();
		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );

		$result = $upgrader->install( $url );
		if ( \is_wp_error( $result ) ) {
			$result->add_data( array( 'status' => 500 ) );

			return $result;
		}
		if ( \is_wp_error( $skin->result ) ) {
			$skin->result->add_data( array( 'status' => 500 ) );

			return $skin->result;
		}
		if ( $skin->get_errors()->has_errors() ) {
			$error = $skin->get_errors();
			$error->add_data( array( 'status' => 500 ) );

			return $error;
		}
		if ( is_null( $result ) ) {
			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof \WP_Filesystem_Base
				&& \is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors()
			) {
				return new \WP_Error(
					'unable_to_connect_to_filesystem',
					$wp_filesystem->errors->get_error_message(),
					array( 'status' => 500 )
				);
			}

			return new \WP_Error(
				'unable_to_connect_to_filesystem',
				__( 'Unable to connect to the filesystem.', 'wp-module-installer' ),
				array( 'status' => 500 )
			);
		}

		$plugin_file = $upgrader->plugin_info();
		if ( ! $plugin_file ) {
			return new \WP_Error(
				'unable_to_determine_installed_plugin',
				__( 'Unable to determine what plugin was installed.', 'wp-module-installer' ),
				array( 'status' => 500 )
			);
		}

		if ( $should_activate && ! \is_plugin_active( $plugin_file ) ) {
			$status = \activate_plugin( $plugin_file );
			if ( \is_wp_error( $status ) ) {
				$status->add_data( array( 'status' => 500 ) );

				return $status;
			}
		}

		// Install translations.
		$installed_locales = array_values( get_available_languages() );
		/** This filter is documented in wp-includes/update.php */
		$installed_locales = apply_filters( 'plugins_update_check_locales', $installed_locales );

		if ( ! empty( $language_packs ) ) {
			$language_packs = array_map(
				static function ( $item ) {
					return (object) $item;
				},
				$language_packs
			);

			$language_packs = array_filter(
				$language_packs,
				static function ( $pack ) use ( $installed_locales ) {
					return in_array( $pack->language, $installed_locales, true );
				}
			);

			if ( $language_packs ) {
				$lp_upgrader = new \Language_Pack_Upgrader( $skin );

				// Install all applicable language packs for the plugin.
				$lp_upgrader->bulk_upgrade( $language_packs );
			}
		}

		return new \WP_REST_Response(
			array(),
			200
		);
	}

	/**
	 * Checks if a given slug is a valid nfd_slug. Ref: includes/Data/Plugins.php for nfd_slug.
	 *
	 * @param string $plugin Slug of the plugin.
	 * @return boolean
	 */
	public static function is_nfd_slug( $plugin ) {
		$plugins_list = Plugins::get();
		if ( isset( $plugins_list['nfd_slugs'][ $plugin ]['approved'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Determines if a plugin has already been installed.
	 *
	 * @param string $plugin_path Path to the plugin's header file.
	 * @return boolean
	 */
	public static function is_plugin_installed( $plugin_path ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = \get_plugins();
		if ( ! empty( $all_plugins[ $plugin_path ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the type of plugin slug. Ref: includes/Data/Plugins.php for the different types.
	 *
	 * @param string $plugin The plugin slug to retrieve the type.
	 * @return string
	 */
	public static function get_plugin_type( $plugin ) {
		if ( \wp_http_validate_url( $plugin ) ) {
			return 'urls';
		}
		if ( self::is_nfd_slug( $plugin ) ) {
			return 'nfd_slugs';
		}
		return 'wp_slugs';
	}

	/**
	 * Get the path to the Plugin's header file.
	 *
	 * @param string $plugin The slug of the plugin.
	 * @param string $plugin_type The type of plugin.
	 * @return string|false
	 */
	public static function get_plugin_path( $plugin, $plugin_type ) {
		$plugin_list = Plugins::get();
		return isset( $plugin_list[ $plugin_type ][ $plugin ] ) ? $plugin_list[ $plugin_type ][ $plugin ]['path'] : false;
	}

	/**
	 * Checks if a plugin with the given slug and activation criteria already exists.
	 *
	 * @param string  $plugin The slug of the plugin to check for
	 * @param boolean $is_active The activation criteria.
	 * @return boolean
	 */
	public static function exists( $plugin, $is_active ) {
		$plugin_type = self::get_plugin_type( $plugin );
		$plugin_path = self::get_plugin_path( $plugin, $plugin_type );
		if ( ! ( $plugin_path && self::is_plugin_installed( $plugin_path ) ) ) {
			return false;
		}

		if ( $is_active && ! \is_plugin_active( $plugin_path ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Checks if a give plugin is active, given the plugin path.
	 *
	 * @param string $plugin_path The plugin path.
	 * @return boolean
	 */
	public static function is_active( $plugin_path ) {
		return \is_plugin_active( $plugin_path );
	}

	/**
	 * Activates a plugin slug after performing the necessary checks.
	 *
	 * @param string $plugin The plugin slug. Ref: includes/Data/Plugins.php for the slugs.
	 * @return boolean
	 */
	public static function activate( $plugin ) {
		$plugin_type = self::get_plugin_type( $plugin );
		$plugin_path = self::get_plugin_path( $plugin, $plugin_type );
		if ( ! ( $plugin_path && self::is_plugin_installed( $plugin_path ) ) ) {
			return false;
		}

		if ( \is_plugin_active( $plugin_path ) ) {
			return true;
		}

		return \activate_plugin( $plugin_path );
		// handle post install callback here.
	}

	/**
	 * Deactivates a given plugin slug after performing the necessary checks.
	 *
	 * @param string $plugin The plugin slug. Ref: includes/Data/Plugins.php for the slugs.
	 * @return boolean
	 */
	public static function deactivate( $plugin ) {
		$plugin_type = self::get_plugin_type( $plugin );
		$plugin_path = self::get_plugin_path( $plugin, $plugin_type );
		if ( ! ( $plugin_path && self::is_plugin_installed( $plugin_path ) ) ) {
			return false;
		}

		if ( ! \is_plugin_active( $plugin_path ) ) {
			return true;
		}

		\deactivate_plugins( $plugin_path );
		return true;
		// handle post install callback here.
	}

	/**
	 * Install the Endurance Page Cache Plugin
	 *
	 * [TODO] Make this generic for mu-plugins and direct file downloads.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function install_endurance_page_cache() {
		if ( ! self::connect_to_filesystem() ) {
			return new \WP_Error(
				'nfd_installer_error',
				__( 'Could not connect to the filesystem.', 'wp-module-installer' ),
				array( 'status' => 500 )
			);
		}

		global $wp_filesystem;

		$plugin_list = Plugins::get();
		$plugin_url  = $plugin_list['nfd_slugs']['nfd_slug_endurance_page_cache']['url'];
		$plugin_path = $plugin_list['nfd_slugs']['nfd_slug_endurance_page_cache']['path'];

		if ( $wp_filesystem->exists( $plugin_path ) ) {
			return new \WP_REST_Response(
				array(),
				200
			);
		}

		if ( ! $wp_filesystem->is_dir( WP_CONTENT_DIR . '/mu-plugins' ) ) {
			$wp_filesystem->mkdir( WP_CONTENT_DIR . '/mu-plugins' );
		}

		$request = \wp_remote_get( $plugin_url );
		if ( \is_wp_error( $request ) ) {
			return $request;
		}

		$wp_filesystem->put_contents( $plugin_path, $request['body'], FS_CHMOD_FILE );

		return new \WP_REST_Response(
			array(),
			200
		);
	}

	/**
	 * Establishes a connection to the wp_filesystem.
	 *
	 * @return boolean
	 */
	protected static function connect_to_filesystem() {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// We want to ensure that the user has direct access to the filesystem.
		$access_type = \get_filesystem_method();
		if ( 'direct' !== $access_type ) {
			return false;
		}

		$creds = \request_filesystem_credentials( site_url() . '/wp-admin', '', false, false, array() );

		if ( ! \WP_Filesystem( $creds ) ) {
			return false;
		}

		return true;
	}

		/**
		 * Verify caller has permissions to install plugins.
		 *
		 * @param \WP_REST_Request $request the incoming request object.
		 *
		 * @return boolean
		 */
	public static function check_install_permissions( \WP_REST_Request $request ) {
		$install_hash = $request->get_header( 'X-NFD-INSTALLER' );
		return self::rest_verify_plugin_install_hash( $install_hash )
			&& Permissions::rest_is_authorized_admin();
	}

		/**
		 * Retrieve Plugin Install Hash Value.
		 *
		 * @return string
		 */
	public static function rest_get_plugin_install_hash() {
		return 'NFD_INSTALLER_' . hash( 'sha256', NFD_INSTALLER_VERSION . wp_salt( 'nonce' ) . site_url() );
	}

	/**
	 * Verify Plugin Install Hash Value.
	 *
	 * @param string $hash Hash Value.
	 * @return boolean
	 */
	public static function rest_verify_plugin_install_hash( $hash ) {
		return self::rest_get_plugin_install_hash() === $hash;
	}

	/**
	 * Retrieves the current status of a plugin.
	 *
	 * @param string $plugin The slug or identifier of the plugin.
	 *
	 * @return string
	 */
	public static function get_plugin_status( $plugin ) {
		$plugin_type         = self::get_plugin_type( $plugin );
		$plugin_path         = self::get_plugin_path( $plugin, $plugin_type );
		$plugin_status_codes = Plugins::get_status_codes();

		if ( ! $plugin_path ) {
			return $plugin_status_codes['unknown'];
		}

		if ( is_plugin_active( $plugin_path ) ) {
			return $plugin_status_codes['active'];
		}

		if ( self::is_plugin_installed( $plugin_path ) ) {
			return $plugin_status_codes['installed'];
		}

		return $plugin_status_codes['not_installed'];
	}
}
