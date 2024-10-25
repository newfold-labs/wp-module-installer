// External Imports
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';
import {
	createInterpolateElement,
	useRef,
	useState,
	useEffect,
} from '@wordpress/element';

// Internal Imports
import { errorIcon, loadingInstaller } from '../../static/icons/index';
import {
	INSTALLER_DIV,
	installerAPI,
	pluginInstallHash,
} from '../../constants';

const Modal = ( {
	action,
	pluginDownloadUrl,
	pluginName,
	pluginProvider,
	pluginSlug,
	redirectUrl,
} ) => {
	/**
	 * Represents the status of the plugin installation process.
	 *
	 * @typedef {('unknown'|'installing'|'failed'|'completed')} PluginStatus
	 *
	 * @property {'unknown'}    unknown    - The plugin installation has not started yet.
	 * @property {'installing'} installing - The plugin installation process has started.
	 * @property {'failed'}     failed     - The plugin installation process failed.
	 * @property {'completed'}  completed  - The plugin installation process is complete.
	 */
	const [ pluginStatus, setPluginStatus ] = useState( 'unknown' );
	const [ show, showModal ] = useState( true );
	const modalRef = useRef( null );

	useEffect( () => {
		document.getElementById( INSTALLER_DIV ).style.display = show
			? 'block'
			: 'none';
	}, [ show ] );

	useEffect( () => {
		switch ( action ) {
			case 'installFreePlugin':
				installFreePlugin();
				break;

			case 'installPremiumPlugin':
				installPremiumPlugin();
				break;
		}
	}, [ action ] );

	const handleKeyDown = ( event ) => {
		if ( event.key === 'Escape' ) {
			showModal( false );
		}
	};

	const handleClickOutside = ( event ) => {
		if (
			pluginStatus === 'failed' && // only close on outside click when in failed state
			modalRef.current &&
			! modalRef.current.contains( event.target )
		) {
			showModal( false );
		}
	};

	useEffect( () => {
		document.addEventListener( 'keydown', handleKeyDown );
		document.addEventListener( 'mousedown', handleClickOutside );

		return () => {
			document.removeEventListener( 'keydown', handleKeyDown );
			document.removeEventListener( 'mousedown', handleClickOutside );
		};
	}, [ pluginStatus ] );

	// Function to handle premium plugin installation
	const installDependantPlugins = async () => {
		try {
			// TODO: Change this logic to ensure we get dependent plugins as a prop
			if ( pluginProvider === 'yith' ) {
				await apiFetch( {
					url: installerAPI,
					method: 'POST',
					headers: {
						'X-NFD-INSTALLER': pluginInstallHash,
					},
					data: {
						activate: true,
						queue: false,
						priority: 0,
						plugin: 'woocommerce',
					},
				} );
			} else if ( pluginProvider === 'yoast' ) {
				// TODO: This will cause 2 calls to install the Yoast SEO Plugin. Remove this once we have dependent plugins as a prop.
				await apiFetch( {
					url: installerAPI,
					method: 'POST',
					headers: {
						'X-NFD-INSTALLER': pluginInstallHash,
					},
					data: {
						activate: true,
						queue: false,
						priority: 0,
						plugin: 'wordpress-seo',
					},
				} );
			}
		} catch ( error ) {
			throw error;
		}
	};

	const installPremiumPlugin = async () => {
		try {
			setPluginStatus( 'installing' );
			await installDependantPlugins();
			await apiFetch( {
				url: installerAPI,
				method: 'POST',
				headers: {
					'X-NFD-INSTALLER': pluginInstallHash,
				},
				data: {
					activate: true,
					queue: false,
					priority: 0,
					premium: true,
					plugin: pluginSlug,
					provider: pluginProvider,
				},
			} );
			setPluginStatus( 'completed' );
			window.location.href = redirectUrl;
			showModal(false);
		} catch ( e ) {
			setPluginStatus( 'failed' );
		}
	};

	const installFreePlugin = async () => {
		try {
			setPluginStatus( 'installing' );
			await installDependantPlugins();
			await apiFetch( {
				url: installerAPI,
				method: 'POST',
				headers: {
					'X-NFD-INSTALLER': pluginInstallHash,
				},
				data: {
					activate: true,
					queue: false,
					priority: 0,
					plugin: pluginDownloadUrl,
				},
			} );
			setPluginStatus( 'completed' );
			showModal( false );
			window.location.href = redirectUrl;
		} catch ( e ) {
			setPluginStatus( 'failed' );
		}
	};

	const helpLink = `${ window.NewfoldRuntime.adminUrl }admin.php?page=${ window.NewfoldRuntime.plugin.brand }#/help`;

	const errorMessage = createInterpolateElement(
		__(
			'Sorry, there was an error installing and activating the plugin. Please try again. If the problem persists, <a>contact support</a>.',
			'wp-module-installer'
		),
		{
			// eslint-disable-next-line jsx-a11y/anchor-has-content
			a: <a href={ helpLink } onClick={ () => showModal( false ) } />,
		}
	);

	return (
		<div className="nfd-installer-modal">
			<div ref={ modalRef } className="nfd-installer-modal__content">
				<div className="nfd-installer-modal__content-heading">
					{ __(
						'Hold on while we get things setup for you!',
						'wp-module-installer'
					) }
				</div>
				<div className="nfd-installer-modal__content-section">
					<img
						src={ loadingInstaller }
						alt={ __( 'Loading Vector.', 'wp-module-installer' ) }
						className="nfd-installer-modal__content-image"
					/>
					{ pluginStatus === 'installing' && (
						<>
							<div className="nfd-installer-modal__content-subheading">
								{ sprintf(
									/* translators: %s: Plugin Name */
									__(
										'Activating %s',
										'wp-module-installer'
									),
									pluginName
								) }
							</div>
							<div className="nfd-installer-modal__loader"></div>
						</>
					) }
					{ pluginStatus === 'completed' && (
						<>
							<div className="nfd-installer-modal__content-subheading">
								{ __(
									'Activation Complete! Redirecting…',
									'wp-module-installer'
								) }
							</div>
							<div className="nfd-installer-modal__loader"></div>
						</>
					) }
					{ pluginStatus === 'failed' && (
						<div className="nfd-installer-modal__content-error">
							<img
								src={ errorIcon }
								alt={ __(
									'Error Icon.',
									'wp-module-installer'
								) }
								className="nfd-installer-modal__content-error--icon"
							/>
							<div>{ errorMessage }</div>
						</div>
					) }
				</div>
			</div>
		</div>
	);
};

export default Modal;
