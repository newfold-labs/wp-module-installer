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
		if ( modalRef.current && ! modalRef.current.contains( event.target ) ) {
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

	const installPremiumPlugin = async () => {
		try {
			setPluginStatus( 'installing' );
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
			showModal( false );
			window.location.href = redirectUrl;
		} catch ( e ) {
			setPluginStatus( 'failed' );
		}
	};

	const installFreePlugin = async () => {
		try {
			setPluginStatus( 'installing' );
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
			'wp-module-onboarding'
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
						alt={ __( 'Loading Vector.', 'wp-module-onboarding' ) }
						className="nfd-installer-modal__content-image"
					/>
					{ pluginStatus === 'installing' && (
						<>
							<div className="nfd-installer-modal__content-subheading">
								{ sprintf(
									/* translators: %s: Plugin Name */
									__(
										'Activating the %s',
										'wp-module-onboarding'
									),
									pluginName
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
