module.exports = function ( grunt ) {
	'use strict';

	// Load all grunt tasks matching the `grunt-*` pattern.
	require('load-grunt-tasks')(grunt);

	// Show elapsed time.
	require('@lodder/time-grunt')(grunt);

	// Project configuration.
	grunt.initConfig(
		{
			package: grunt.file.readJSON( 'package.json' ),
			addtextdomain: {
				options: {
					expand: true,
					text_domain: 'wc-min-max-quantities',
					updateDomains: [ 'framework-text-domain' ],
				},
				plugin: {
					files: {
						src: [
							'*.php',
							'**/*.php',
							'!node_modules/**',
							'!tests/**',
							'!vendor/**',
						],
					},
				},
			},
			makepot: {
				target: {
					options: {
						domainPath: 'languages',
						exclude: [ 'packages/*', '.git/*', 'node_modules/*', 'tests/*', 'vendor/*' ],
						mainFile: 'wc-min-max-quantities.php',
						potFilename: 'wc-min-max-quantities.pot',
						potHeaders: {
							poedit: true,
							'x-poedit-keywordslist': true,
						},
						type: 'wp-plugin',
						updateTimestamp: false,
					},
				},
			},
			wp_readme_to_markdown: {
				your_target: {
					files: {
						'readme.md': 'readme.txt',
					},
				},
			},
		}
	);

	grunt.registerTask('i18n', ['addtextdomain', 'makepot']);
	grunt.registerTask('build', ['i18n']);
};
