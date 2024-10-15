// External Imports
import domReady from '@wordpress/dom-ready';

// Internal Imports
import { INSTALLER_DIV } from '../Installer/constants';

domReady( () => {

	function renderModal(
		pluginName,
		pluginSlug,
		pluginProvider,
		pluginURL,
		activate
	) {
		// create the installer div
		document.getElementById( INSTALLER_DIV ).style.display = 'block';
		document
			.getElementById( INSTALLER_DIV )
			.setAttribute( 'nfd-installer-app__plugin--name', pluginName );
		document
			.getElementById( INSTALLER_DIV )
			.setAttribute( 'nfd-installer-app__plugin--slug', pluginSlug );
		document
			.getElementById( INSTALLER_DIV )
			.setAttribute(
				'nfd-installer-app__plugin--provider',
				pluginProvider
			);
		document
			.getElementById( INSTALLER_DIV )
			.setAttribute( 'nfd-installer-app__plugin--url', pluginURL );
		document
			.getElementById( INSTALLER_DIV )
			.setAttribute(
				'nfd-installer-ap__plugin--activate',
				activate === 'true' ? true : false
			);
		window.dispatchEvent( new Event( 'installerParamsSet' ) );
	}

	const domObserver = new window.MutationObserver( ( mutationList ) => {
		for ( const mutation of mutationList ) {
			if ( mutation.type === 'childList' ) {
				for ( const addedNode of mutation.addedNodes ) {
					if (
						typeof addedNode === 'object' &&
						typeof addedNode.querySelectorAll === 'function'
					) {
						addedNode
							.querySelectorAll(
								'[data-nfd-installer-plugin-provider]'
							)
							.forEach( ( ele ) => {
								ele.addEventListener( 'click', function ( e ) {
									if (
										e.target.getAttribute(
											'data-nfd-installer-plugin-slug'
										) !== null
									) {
										renderModal(
											this.getAttribute(
												'data-nfd-installer-plugin-name'
											),
											this.getAttribute(
												'data-nfd-installer-plugin-slug'
											),
											this.getAttribute(
												'data-nfd-installer-plugin-provider'
											),
											this.getAttribute(
												'data-nfd-installer-plugin-url'
											),
											this.getAttribute(
												'data-nfd-installer-plugin-activate'
											)
										);
									}
								} );
							} );
					}
				}
			}
		}
	} );

	domObserver.observe( document.body, { childList: true, subtree: true } );
} );
