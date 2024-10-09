const path = require( 'path' );
const { merge } = require( 'webpack-merge' );
const wpScriptsConfig = require( '@wordpress/scripts/config/webpack.config' );
const version = require( './package.json' ).version; // never require full config!

const nfdSurveyWebpackConfig = {
	output: {
		path: path.resolve( process.cwd(), `build/${ version }` ),
		library: [ 'newfold', 'Installer', '[name]' ],
		libraryTarget: 'window',
	},
};

module.exports = merge( wpScriptsConfig, nfdSurveyWebpackConfig );
