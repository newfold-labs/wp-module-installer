// External Imports
import { useState, useEffect } from '@wordpress/element';

// Internal Imports
import Modal from '../Modal';

const App = () => {
	const [ action, setAction ] = useState();
	const [ statesReady, setStatesReady ] = useState( false );
	const [ pluginBasename, setPluginBasename ] = useState();
	const [ pluginName, setPluginName ] = useState();
	const [ pluginDownloadUrl, setPluginDownloadUrl ] = useState();
	const [ pluginProvider, setPluginProvider ] = useState();
	const [ pluginSlug, setPluginSlug ] = useState();
	const [ redirectUrl, setRedirectUrl ] = useState();

	const setData = ( e ) => {
		setStatesReady( false );
		setAction( e.detail.action );
		setPluginBasename( e.detail.pluginBasename );
		setPluginName( e.detail.pluginName );
		setPluginDownloadUrl( e.detail.pluginDownloadUrl );
		setPluginProvider( e.detail.pluginProvider );
		setPluginSlug( e.detail.pluginSlug );
		setRedirectUrl( e.detail.redirectUrl );
		setStatesReady( true );
	};

	useEffect( () => {
		// Add an event listener to get the changes
		window.addEventListener( 'installerParamsSet', setData );

		// Cleanup the event listener
		return () => {
			window.removeEventListener( 'installerParamsSet', setData );
		};
	}, [] );

	return (
		<div className="nfd-installer-app">
			{ statesReady && (
				<Modal
					action={ action }
					pluginBasename={ pluginBasename }
					pluginName={ pluginName }
					pluginDownloadUrl={ pluginDownloadUrl }
					pluginProvider={ pluginProvider }
					pluginSlug={ pluginSlug }
					redirectUrl={ redirectUrl }
				/>
			) }
		</div>
	);
};

export default App;
