// External Imports
import { useState, useEffect } from '@wordpress/element';

// Internal Imports
import Modal from '../Modal';
import { INSTALLER_DIV } from '../../constants';

const App = () => {
	const [ pluginName, setPluginName ] = useState();
	const [ pluginSlug, setPluginSlug ] = useState();
	const [ pluginProvider, setPluginProvider ] = useState();
	const [ pluginURL, setPluginURL ] = useState();
	const [ pluginActivate, setPluginActivate ] = useState();

	useEffect( () => {
		// Add an event listener to get the changes
		window.addEventListener( 'installerParamsSet', getData );

		// Cleanup the event listener
		return () => {
			window.removeEventListener( 'installerParamsSet', getData );
		};
	}, [] );

	const getData = () => {
		const element = document.getElementById( INSTALLER_DIV );
		setPluginName(
			element.getAttribute( 'nfd-installer-app__plugin--name' )
		);
		setPluginSlug(
			element.getAttribute( 'nfd-installer-app__plugin--slug' )
		);
		setPluginProvider(
			element.getAttribute( 'nfd-installer-app__plugin--provider' )
		);
		setPluginURL(
			element.getAttribute( 'nfd-installer-app__plugin--url' )
		);
		setPluginActivate(
			element.getAttribute( 'nfd-installer-app__plugin--activate' )
		);
	};

	return (
		<div className="nfd-installer-app">
			{ pluginSlug && (
				<Modal
					pluginName={ pluginName }
					pluginSlug={ pluginSlug }
					pluginURL={ pluginURL }
					pluginActivate={ pluginActivate }
					pluginProvider={ pluginProvider }
				/>
			) }
		</div>
	);
};

export default App;
