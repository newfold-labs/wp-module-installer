// External Imports
import domReady from '@wordpress/dom-ready';

// Internal Imports
import { INSTALLER_DIV } from '../Installer/constants';

domReady( () => {
	// function removeModal() {
	// 	// find the modal and remove if it exists
	// 	const modal = document.querySelector( '.nfd-installer' );
	// 	if ( modal ) {
	// 		modal.remove();
	// 	}
	// }

	function renderModal( pluginName, pluginSlug, pluginURL, activate ) {
		// Don't make requests if values not provided.
		if (
			'' === pluginName ||
			'' === pluginSlug ||
			'' === pluginURL ||
			'' === activate
		) {
			return;
		}

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
			.setAttribute( 'nfd-installer-app__plugin--url', pluginURL );
		document
			.getElementById( INSTALLER_DIV )
			.setAttribute(
				'nfd-installer-app__plugin--activate',
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
								'[data-nfd-installer-pls-provider]'
							)
							.forEach( ( ele ) => {
								console.log("hereee");
								
								ele.addEventListener( 'click', function ( e ) {
									if (
										e.target.getAttribute(
											'data-nfd-installer-pls-slug'
										) !== null
									) {
										renderModal(
											this.getAttribute(
												'data-nfd-installer-plugin-name'
											),
											this.getAttribute(
												'data-nfd-installer-pls-slug'
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
