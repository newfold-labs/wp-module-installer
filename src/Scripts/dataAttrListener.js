import domReady from '@wordpress/dom-ready';
import apiFetch from '@wordpress/api-fetch';

import { installerAPI } from '../constants';

domReady( () => {
	const domObserver = new window.MutationObserver( ( mutationList ) => {
	} );

	domObserver.observe( document.body, { childList: true, subtree: true } );
} );
