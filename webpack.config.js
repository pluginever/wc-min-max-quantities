/**
 * External dependencies
 */
const baseConfig = require( '@byteever/scripts/config/webpack.config' );

module.exports = {
	...baseConfig,
	entry: {
		...baseConfig.entry,
		'css/admin': './assets/src/css/admin.scss',
	},
};
