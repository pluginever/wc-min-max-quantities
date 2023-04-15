module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	env: {
		browser: true,
		es6: true,
		node: true,
	},
	globals: {
		wp: true,
		es6: true,
		wcmmq_admin_vars: true,
		wcmmq_vars: true,
	},
	rules: {
		camelcase: 0,
		indent: 0,
		'no-console': 1,
	},
};
