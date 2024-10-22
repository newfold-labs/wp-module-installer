// External Imports
import domReady from '@wordpress/dom-ready';

domReady( () => {
	function dispatchEvent( detail ) {
		window.dispatchEvent(
			new CustomEvent( 'installerParamsSet', { detail } )
		);
	}

	document.body.addEventListener( 'click', ( e ) => {
		const el = e.target;

		if ( el.hasAttribute( 'data-nfd-installer-plugin-name' ) ) {
			// Don't follow the existing link
			e.preventDefault();

			// URL to redirect to after install
			const redirectUrl =
				el.getAttribute( 'href' ) ||
				el.getAttribute( 'data-nfd-installer-plugin-url' );

			// Is free plugin
			if ( el.hasAttribute( 'data-nfd-installer-download-url' ) ) {
				dispatchEvent( {
					action: 'installFreePlugin',
					pluginName: el.getAttribute(
						'data-nfd-installer-plugin-name'
					),
					pluginDownloadUrl: el.getAttribute(
						'data-nfd-installer-download-url'
					),
					pluginProvider: el.getAttribute(
						'data-nfd-installer-pls-provider'
					),
					redirectUrl,
				} );
				return false;
			}

			// Is premium plugin
			if (
				el.hasAttribute( 'data-nfd-installer-pls-slug' ) &&
				el.hasAttribute( 'data-nfd-installer-pls-provider' )
			) {
				dispatchEvent( {
					action: 'installPremiumPlugin',
					pluginName: el.getAttribute(
						'data-nfd-installer-plugin-name'
					),
					pluginSlug: el.getAttribute(
						'data-nfd-installer-pls-slug'
					),
					pluginProvider: el.getAttribute(
						'data-nfd-installer-pls-provider'
					),
					redirectUrl,
				} );
				return false;
			}

			// TODO: Handle use cases for theme installs

			// Redirect to the URL provided by the data attribute
			if ( redirectUrl ) {
				window.location.href = redirectUrl;
			}
		}
	} );
} );
