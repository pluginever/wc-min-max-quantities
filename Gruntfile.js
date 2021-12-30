module.exports = function (grunt) {
	'use strict';
	var sass = require('node-sass');
	// Project configuration
	grunt.initConfig({
		// Set variables.
		vars: {
			css_dir: 'assets/css',
			fonts_dir: 'assets/fonts',
			images_dir: 'assets/images',
			js_dir: 'assets/js',
		},

		// JavaScript linting with JSHint.
		eslint: {
			all: [
				'Gruntfile.js',
				'<%= vars.js_dir %>/*.js',
				'!<%= vars.js_dir %>/*.min.js'
			]
		},

		// Minify .js files.
		uglify: {
			options: {
				ie8: true,
				parse: {
					strict: false
				},
				output: {
					comments: /@license|@preserve|^!/
				}
			},
			dist: {
				files: [{
					expand: true,
					cwd: '<%= vars.js_dir %>/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= vars.js_dir %>/',
					ext: '.min.js'
				}]
			},
			vendor: {
				files: {
					'<%= vars.js_dir %>/a11y-dialog.min.js': ['node_modules/a11y-dialog/dist/a11y-dialog.js']
				}
			}
		},

		// Compile all .scss files.
		sass: {
			options: {
				implementation: sass,
				sourceMap: true,
				outputStyle: 'expanded'
			},
			dist: {
				files: [{
					expand: true,
					cwd: '<%= vars.css_dir %>/',
					src: ['*.scss'],
					dest: '<%= vars.css_dir %>/',
					ext: '.css'
				}]
			}
		},

		// Autoprefixer.
		postcss: {
			options: {
				map: true,
				processors: [
					require('autoprefixer')()
				]
			},
			dist: {
				src: [
					'<%= vars.css_dir %>/*.css',
					'!<%= vars.css_dir %>/*.min.css'
				]
			}
		},

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: '<%= vars.css_dir %>/',
				src: ['*.css', '!*.min.css'],
				dest: '<%= vars.css_dir %>/',
				ext: '.min.css'
			}
		},

		// Check textdomain errors.
		checktextdomain: {
			options: {
				text_domain: 'wc-min-max-quantities',
				report_missing: true,
				correct_domain: true,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [
					'**/*.php',
					'!node_modules/**',
					'!tests/**',
					'!vendor/**',
					'!bin/**'
				],
				expand: true
			}
		},

		makepot: {
			target: {
				options: {
					domainPath: 'i18n/languages',
					exclude: ['.git/*', 'bin/*', 'node_modules/*', 'tests/*'],
					mainFile: 'wc-min-max-quantities.php',
					potFilename: 'wc-min-max-quantities.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},

		// Watch changes for assets.
		watch: {
			css: {
				files: ['<%= vars.css_dir %>/**/*.scss'],
				tasks: ['sass', 'postcss', 'cssmin']
			},
			js: {
				files: [
					'<%= vars.js_dir %>/**/*js',
					'!<%= vars.js_dir %>/**/*.min.js'
				],
				tasks: ['eslint', 'uglify']
			}
		},

		// Verify build
		shell: {
			command: ['rm -rf @next', 'npm install', 'npm run build', 'rsync -rc --exclude-from="./.distignore" "." "./@next/" --delete --delete-excluded', 'echo ', 'echo === NOW COMPARE WITH ORG/GIT VERSION==='].join(' && ')
		}
	});

	// Saves having to declare each dependency
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Register tasks.
	grunt.registerTask('default', ['build']);
	grunt.registerTask('build', ['eslint', 'uglify', 'sass', 'postcss', 'cssmin', 'checktextdomain', 'makepot']);
	grunt.util.linefeed = '\n';
};
