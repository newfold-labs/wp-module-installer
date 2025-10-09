// External Imports
import { useState, useEffect } from '@wordpress/element';

// Internal Imports
import Modal from '../Modal';
import {INSTALLER_DIV} from "../../constants";
import { set } from 'lodash';

const App = () => {
	const [ action, setAction ] = useState();
	const [ pluginBasename, setPluginBasename ] = useState();
	const [ pluginName, setPluginName ] = useState();
	const [ pluginDownloadUrl, setPluginDownloadUrl ] = useState();
	const [ pluginProvider, setPluginProvider ] = useState();
	const [ pluginSlug, setPluginSlug ] = useState();
	const [ redirectUrl, setRedirectUrl ] = useState();
	const [ pluginDependency, setPluginDependency ] = useState();
    const [open, setOpen] = useState(false);
    const handleOpen = () => setOpen(true);
    const handleClose = () => setOpen(false);

	const setData = ( e ) => {
		setAction( e.detail.action );
		setPluginBasename( e.detail.pluginBasename );
		setPluginName( e.detail.pluginName );
		setPluginDownloadUrl( e.detail.pluginDownloadUrl );
		setPluginProvider( e.detail.pluginProvider );
		setPluginSlug( e.detail.pluginSlug );
		setRedirectUrl( e.detail.redirectUrl );
		setPluginDependency( e.detail.pluginDependency );
        handleOpen();
	};

	useEffect( () => {
		// Add an event listener to get the changes
		window.addEventListener( 'installerParamsSet', setData );

		// Cleanup the event listener
		return () => {
			window.removeEventListener( 'installerParamsSet', setData );
		};
	}, [] );

    useEffect( () => {
        document.getElementById( INSTALLER_DIV ).style.display = open
            ? 'block'
            : 'none';
    }, [ open ] );

	return (
		<div className="nfd-installer-app">
			{ open && (
				<Modal
					action={ action }
					pluginBasename={ pluginBasename }
					pluginName={ pluginName }
					pluginDownloadUrl={ pluginDownloadUrl }
					pluginProvider={ pluginProvider }
					pluginSlug={ pluginSlug }
					redirectUrl={ redirectUrl }
					pluginDependency={ pluginDependency }
                    onClose={handleClose}
				/>
			) }
		</div>
	);
};

export default App;
