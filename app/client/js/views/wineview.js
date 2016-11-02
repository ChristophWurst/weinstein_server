/* global Marionette, Handlebars */

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

var Weinstein = Weinstein || {};

(function (Weinstein, Marionette, Handlebars) {
	'use strict';

	Weinstein.Views = Weinstein.Views || {};

	var WINE_TEMPLATE = '' +
		'<td class="text-center">{{#if nr}}<a href="/wines/{{id}}">{{nr}}</a>{{else}}-{{/if}}</td>' +
		'<td><a href="/settings/applicant/{{applicant.id}}">{{applicant.label}} {{applicant.lastname}}</a></td>' +
		'<td><a href="/settings/association/{{applicant.association.id}}">{{applicant.association.name}}</a></td>' +
		'<td>{{ label }}</td>' +
		'<td><{{ winesort.name }}</td>' +
		'<td>{{ vintage }}</td>' +
		'<td class="text-center">' +
		'	{{#if winequality}}{{ winequality.abbr }}{{else}}-{{/if}}' +
		'</td>' +
		'<td class="text-center">{{ l10nFloat alcohol }}</td>' +
		'<td class="text-center">{{#if alcoholtot}}{{ l10nFloat alcoholtot }}{{else}}-{{/if}}</td>' +
		'<td class="text-center">{{ l10nFloat sugar }}</td>' +
		'<td class="text-center">{{#if rating1}}{{ l10nFloat rating1 }}{{else}}-{{/if}}</td>' +
		'<td class="text-center">{{#if rating2}}{{ l10nFloat rating2 }}{{else}}-{{/if}}</td>' +
		'<td class="text-center wine_kdb">' +
		'    {{#if kdb}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>' +
		'<td class="text-center wine_excluded">' +
		'    {{#if excluded}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>' +
		'<td class="text-center wine_sosi">' +
		'    {{#if sosi}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>' +
		'<td class="text-center wine_chosen">' +
		'    {{#if chosen}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>' +
		'<td>|</td>' +
		'<td class="text-center">' +
		'    ' +
		'</td>';

	var WineView = Marionette.View.extend({
		tagName: 'tr',
		template: Handlebars.compile(WINE_TEMPLATE)
	});

	var WineListView = Marionette.CollectionView.extend({
		tagName: 'tbody',
		childView: WineView
	});

	var WineView = Marionette.View.extend({
		_wines: null,
		/**
		 * @param {object} options
		 */
		initialize: function (options) {
			this._wines = options.wines;
		},
		render: function () {
			var listView = new WineListView({
				el: this.$('#wine_list'),
				collection: this._wines
			});
			listView.render();
		}
	});

	Weinstein.Views.WineView = WineView;

})(Weinstein, Marionette, Handlebars);

