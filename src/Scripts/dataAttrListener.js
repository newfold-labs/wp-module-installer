import domReady from '@wordpress/dom-ready';
import apiFetch from '@wordpress/api-fetch';

import { pluginInstallHash, installerAPI } from '../Installer/constants';

domReady( () => {
	const installPremiumPlugin = async ( pluginSlug, activate ) => {
		const data = await apiFetch( {
			url: installerAPI,
			method: 'POST',
			headers: {
				'X-NFD-INSTALLER': pluginInstallHash,
			},
			data: {
				plugin: pluginSlug,
				activate: activate === 'true' ? true : false,
				queue: false,
				priority: 0,
				premium: true,
			},
		} );
		return data;
	};

	// function removeModal() {
	// 	// find the modal and remove if it exists
	// 	const modal = document.querySelector( '.nfd-installer' );
	// 	if ( modal ) {
	// 		modal.remove();
	// 	}
	// }

	// function renderModal() {
	// 	// create the installer div
	// 	const modal = document.createElement( 'div' );
	// 	modal.classList.add( 'nfd-installer' );
	// 	document.body.appendChild( modal );
	// }

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
										// renderModal();
										installPremiumPlugin(
											this.getAttribute(
												'data-nfd-installer-plugin-slug'
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
