import domReady from '@wordpress/dom-ready';
import apiFetch from '@wordpress/api-fetch';

import { installerAPI } from '../constants';

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
							.querySelectorAll( '[data-nfd-plugin-provider]' )
							.forEach( ( ele ) => {
								ele.addEventListener( 'click', function ( e ) {
									if (
										e.target.getAttribute(
											'data-nfd-plugin-slug'
										) !== null
									) {
										apiFetch( {
											url: installerAPI,
											method: 'POST',
											data: {
												plugin: this.getAttribute(
													'data-nfd-plugin-slug'
												),
												activate:
													this.getAttribute(
														'data-nfd-plugin-activate'
													) === 'true'
														? true
														: false,
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
