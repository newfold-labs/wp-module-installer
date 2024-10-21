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