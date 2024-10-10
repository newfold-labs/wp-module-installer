import { __ } from '@wordpress/i18n';
import { loadingInstaller } from '../../static/icons/index';

const Modal = ( {} ) => {
	return (
		<div className="nfd-installer-modal">
			<div className="nfd-installer-modal__content">
				<div className="nfd-installer-modal__content-heading">
					{ __(
						'Hold on while we get things setup for you!',
						'wp-module-installer'
					) }
				</div>
				<div className="nfd-installer-modal__content-section">
					<img
						src={ loadingInstaller }
						alt="Man carrying items"
						className="nfd-installer-modal__content-image"
					/>
					<div className="nfd-installer-modal__content-subheading">
						{ __(
							'Activating the plugin_name…',
							'wp-module-installer'
						) }
					</div>
					<div className="nfd-installer-modal__loader"></div>
				</div>
			</div>
		</div>
	);
};

export default Modal;
