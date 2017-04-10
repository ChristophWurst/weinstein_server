/* global module */

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License,version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

module.exports = function (grunt) {
	grunt.initConfig({
		uglify: {
			options: {
				sourceMap: true
			},
			build: {
				src: [
					'app/client/js/**/*.js',
					'!app/client/js/tests/**/*.js'
				],
				dest: 'public/js/weinstein.js'
			}
		},
		watch: {
			uglify: {
				files: [
					'<%= uglify.build.src %>'
				],
				tasks: [
					'uglify'
				]
			}
		},
		less: {
			production: {
				files: {
					'public/css/weinstein.css': 'app/client/less/weinstein.less',
					'public/css/bootstrap.css': 'app/client/less/bootstrap.less',
					'public/css/bootstrap-theme.css': 'app/client/less/theme.less'
				}
			}
		},
		karma: {
			unit: {
				configFile: 'karma.conf.js',
				autoWatch: true
			},
			continuous: {
				configFile: 'karma.conf.js',
				browsers: ['PhantomJS'],
				singleRun: true,
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Karma unit tests
	grunt.loadNpmTasks('grunt-karma');

	grunt.registerTask('dev', ['watch']);
	grunt.registerTask('default', ['uglify', 'less']);
};
