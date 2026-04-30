const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require("path");
const CopyPlugin = require("copy-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
	...defaultConfig,
	entry: {
		...(() => {
			const entry = typeof defaultConfig.entry === 'function' ? defaultConfig.entry() : defaultConfig.entry;
			return entry || {};
		})(),
		"plugins/shortlink-sidebar": "./src/plugins/shortlink-sidebar/index.js",
		"admin-bar/shortlink-modal": "./src/admin-bar/shortlink-modal.js",
		"shortlink-manager": "./src/admin/shortlink-manager.js"
	},
	module: {
		...defaultConfig.module,
		rules: [...defaultConfig.module.rules],
	},
	plugins: [
		...defaultConfig.plugins,
		new MiniCssExtractPlugin({ filename: "[name].css" }),
		new CopyPlugin({
			patterns: [
				{
					from: "src/bts/**",
					to({ context, absoluteFilename }) {
						return "js/[name][ext]";
					},
					filter: (resourcePath) => {
						return resourcePath.includes(".js");
					},
				},
				{
					from: "src/admin/**",
					to({ context, absoluteFilename }) {
						return "admin/[name][ext]";
					},
					filter: (resourcePath) => {
						return resourcePath.includes(".css");
					},
				},
			],
		}),
	],
};

console.log("PATH OUT", path.resolve(__dirname, "src", "admin"));
