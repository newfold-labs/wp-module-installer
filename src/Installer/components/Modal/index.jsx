// External Imports
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';
import { Icon, info } from '@wordpress/icons';
import {
	createInterpolateElement,
	useRef,
	useState,
	useEffect,
} from '@wordpress/element';

// Internal Imports
import { loadingInstaller } from '../../static/icons/index';
import {
	INSTALLER_DIV,
	installerAPI,
	pluginInstallHash,
} from '../../constants';

const Modal = ( {
	pluginName,
	pluginSlug,
	pluginURL,
	pluginActivate,
	pluginProvider,
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
	// const [ isRequestCompleted, setIsRequestCompleted ] = useState( false );
	const modalRef = useRef( null );

	useEffect( () => {
		installPremiumPlugin();
	}, [ pluginSlug ] );

	const handleKeyDown = ( event ) => {
		if ( event.key === 'Escape' ) {
			closeModal();
		}
	};

	const handleClickOutside = ( event ) => {
		if ( modalRef.current && ! modalRef.current.contains( event.target ) ) {
			closeModal();
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

	const closeModal = () => {
		if ( 'failed' === pluginStatus || 'completed' === pluginStatus ) {
			document.getElementById( INSTALLER_DIV ).style.display = 'none';
		}
	};

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
					activate: pluginActivate === 'true' ? true : false,
					queue: false,
					priority: 0,
					premium: true,
					plugin: pluginSlug,
					provider: pluginProvider,
				},
			} );
			setPluginStatus( 'completed' );
			window.open( pluginURL, '_self' );
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
			a: <a href={ helpLink } />,
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
										'Activating… %s',
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
							<Icon
								className="nfd-installer-modal__content-error--icon"
								icon={ info }
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
