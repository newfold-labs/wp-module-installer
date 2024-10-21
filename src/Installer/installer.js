/**
 * Styles.
 */
import './styles/app.scss';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { INSTALLER_DIV } from './constants';

// eslint-disable-next-line import/no-unresolved
import App from './components/App';

domReady( () => {
	renderModal( INSTALLER_DIV );
} );

/**
 * This function creates a modal that is rendered on the page.
 *
 * @param {string} elementId It takes an elementId as an argument and creates a div with the given elementId.
 */
const renderModal = ( elementId ) => {
	const modalRoot = document.createElement( 'div' );
	modalRoot.id = elementId;
	if ( ! document.getElementById( elementId ) ) {
		// Append the modal container to the body if it hasn't been added already.
		document.body.append( modalRoot );
		if ( 'undefined' !== typeof createRoot ) {
			// WP 6.2+ only
			createRoot(modalRoot ).render( <App />);
		} else if ( 'undefined' !== typeof render ) {
			render( <App />, modalRoot );
		}
	}
};

// Installer API/helpers
{
	const attachToRuntime = () => {
		// window.NewfoldRuntime.comingSoon = buildObject();
		window.NewfoldRuntime.installer = buildObject();
	};

	// attach helper method to runtime
	const buildObject = () => {
		return {
			renderInstallerButton,
		};
	};

	/**
	 * Helper JS method to be used when instantiating buttons to integrate with the installer.
	 *
	 * @param {Object} entitlement the entitlement or product
	 * @param {string} classes     to be added to the button
	 */
	const renderInstallerButton = ( entitlement, classes = '' ) => {
		/*
		entitlementShape = {
			"name": "Advanced Reviews",
			"url": "https://example.com", // redirect on completion
			"cta": {
			"text": "Manage",
			"url": "{siteUrl}/wp-admin/admin.php?page=bluehost#/example"
			},
			"plsProviderName": "yith", // premium provider
			"plsSlug": "yith-woocommerce-advanced-reviews", // premium id
			"download": null, // free download url
			"slug": "example",
			"basename": "example.com/example.php",
		};
		*/
		if (
			! entitlement ||
			! entitlement.cta.url ||
			! entitlement.cta.text ||
			! entitlement.basename ||
			! entitlement.name
		) {
			return;
		}

		let buttonHTML = `<button
			class="${ classes }"
			href="${ renderCTAUrl( entitlement.cta.url ) }"
			data-nfd-installer-plugin-basename="${ entitlement.basename }"
			data-nfd-installer-plugin-name="${ entitlement.name }"
		`;
		// premium attributes
		if ( entitlement.plsProviderName && entitlement.plsSlug ) {
			buttonHTML += `
			data-nfd-installer-pls-slug="${ entitlement.plsSlug }"
			data-nfd-installer-pls-provider="${ entitlement.plsProviderName }"
			`;
		}
		// free attributes
		if ( entitlement.download ) {
			buttonHTML += `
			data-nfd-installer-download-url="${ entitlement.download }"
			`;
		}
		buttonHTML += `>${ entitlement.cta.text }</button>`; // close button element
		return buttonHTML;
	};

	/**
	 * Helper method to clean cta urls for use - replacing {siteUrl} with actual
	 * @param {string} url the url to transform
	 */
	const renderCTAUrl = ( url ) => {
		if ( ! window.NewfoldRuntime || ! window.NewfoldRuntime.siteUrl ) {
			// fallback to site relative url if no base_url is found
			return url.replace( '{siteUrl}', '' );
		}
		return url.replace( '{siteUrl}', window.NewfoldRuntime.siteUrl );
	};

	window.addEventListener( 'DOMContentLoaded', () => {
		attachToRuntime();
	} );

}