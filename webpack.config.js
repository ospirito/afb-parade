const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const CopyPlugin = require( 'copy-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

module.exports = {
	...defaultConfig,
	module: {
		...defaultConfig.module,
		rules: [ ...defaultConfig.module.rules ],
	},
	plugins: [
		...defaultConfig.plugins,
		new MiniCssExtractPlugin( { filename: '[name].css' } ),
		new CopyPlugin( {
			patterns: [
				{
					from: 'src/bts/**',
					to() {
						return 'js/[name][ext]';
					},
					filter: ( resourcePath ) => {
						return resourcePath.includes( '.js' );
					},
				},
				{
					from: 'src/admin/**',
					to() {
						return 'admin/[name][ext]';
					},
					filter: ( resourcePath ) => {
						return resourcePath.includes( '.css' );
					},
				},
			],
		} ),
	],
};
