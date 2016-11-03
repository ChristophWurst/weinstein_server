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

	var WINE_TABLE_TEMPLATE = '' +
		'<table class="table table-striped table-condensed">' +
		'	<thead>' +
		'		<tr>' +
		'			<th class="text-center">Dateinummer</th>' +
		'			<th>Betrieb</th>' +
		'			<th>Verein</th>' +
		'			<th>Marke</th>' +
		'			<th>Sorte</th>' +
		'			<th>Jahr</th>' +
		'			<th class="text-center">Qualit&auml;t</th>' +
		'			<th class="text-center">Alk.</th>' +
		'			<th class="text-center">Alk. ges.</th>' +
		'			<th class="text-center">Zucker</th>' +
		'			{{#if show_rating1 }}<th class="text-center">1. Bewertung</th>{{/if}}' +
		'			{{#if show_rating2 }}<th class="text-center">2. Bewertung</th>{{/if}}' +
		'			{{#if show_kdb }}<th class="text-center">KdB</th>{{/if}}' +
		'			{{#if show_excluded }}<th class="text-center">Ex</th>{{/if}}' +
		'			{{#if show_sosi }}<th class="text-center">SoSi</th>{{/if}}' +
		'			{{#if show_chosen }}<th class="text-center">Ausschank</th>{{/if}}' +
		'			{{#if show_edit_wine}}<th></th>{{/if}}' +
		'			{{#if show_enrollment_pdf_export}}' +
		'			<th class="text-center">Formular</th>' +
		'			{{/if}}' +
		'		</tr>' +
		'	</thead>' +
		'	<tbody id="wine_list">' +
		'	</tbody>' +
		'</table>';

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
		'{{#if show_rating1 }}<td class="text-center">{{#if rating1}}{{ l10nFloat rating1 }}{{else}}-{{/if}}</td>{{/if}}' +
		'{{#if show_rating2 }}<td class="text-center">{{#if rating2}}{{ l10nFloat rating2 }}{{else}}-{{/if}}</td>{{/if}}' +
		'{{#if show_kdb }}<td class="text-center">' +
		'    {{#if kdb}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>{{/if}}' +
		'{{#if show_excluded }}<td class="text-center">' +
		'    {{#if excluded}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>{{/if}}' +
		'{{#if show_sosi }}<td class="text-center">' +
		'    {{#if sosi}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>{{/if}}' +
		'{{#if show_chosen }}<td class="text-center">' +
		'    {{#if chosen}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>{{/if}}' +
		'{{#if show_edit_wine }}<td>|</td>{{/if}}' +
		'{{#if show_enrollment_pdf_export}}' +
		'<td class="text-center">' +
		'    ' +
		'</td>' +
		'{{/if}}';

	var WineView = Marionette.View.extend({
		tagName: 'tr',
		template: Handlebars.compile(WINE_TEMPLATE),
		_tableOptions: {},
		initialize: function (options) {
			this._tableOptions = options.tableOptions;
		},
		viewContext: function () {
			return this._tableOptions;
		}
	});

	var WineListView = Marionette.CollectionView.extend({
		tagName: 'tbody',
		childView: WineView,
		_tableOptions: {},
		initialize: function (options) {
			this._tableOptions = options.tableOptions;
		},
		childViewOptions: function () {
			return {
				tableOptions: this._tableOptions
			};
		}
	});

	var WineView = Marionette.CompositeView.extend({
		template: Handlebars.compile(WINE_TABLE_TEMPLATE),
		_wines: null,
		_tableOptions: {},
		/**
		 * @param {object} options
		 */
		initialize: function (options) {
			this._wines = options.wines;
			this._tableOptions = options.tableOptions;
		},
		viewContext: function () {
			return this._tableOptions;
		},
		onRender: function () {
			var listView = new WineListView({
				el: this.$('#wine_list'),
				collection: this._wines,
				tableOptions: this._tableOptions
			});
			listView.render();
		}
	});

	Weinstein.Views.WineView = WineView;

})(Weinstein, Marionette, Handlebars);

