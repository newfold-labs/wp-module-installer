import domReady from '@wordpress/dom-ready';
import apiFetch from '@wordpress/api-fetch';

import { pluginInstallHash, installerAPI } from '../constants';

domReady( () => {
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
										apiFetch( {
											url: installerAPI,
											method: 'POST',
											headers: {
												'X-NFD-INSTALLER':
													pluginInstallHash,
											},
											data: {
												plugin: this.getAttribute(
													'data-nfd-installer-plugin-slug'
												),
												activate:
													this.getAttribute(
														'data-nfd-installer-plugin-activate'
													) === 'true'
														? true
														: false,
												queue: false,
												priority: 0,
												premium: true,
											},
										} );
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
