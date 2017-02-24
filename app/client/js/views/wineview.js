/* global Marionette, Handlebars, Promise */

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
		'<table class="table table-striped table-condensed wine-table">' +
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
		'</table>' +
		'<div class="container-fluid"><div class="text-center">' +
		'	<button class="btn btn-primary wine-load-more" data-loading-text="Lade...">Mehr laden</button>' +
		'</div></div>';

	var WINE_TEMPLATE = '' +
		'<td class="text-center">{{#if nr}}<a href="/wines/{{id}}">{{nr}}</a>{{else}}-{{/if}}</td>' +
		'<td><a href="/settings/applicant/{{applicant.id}}">{{applicant.label}} {{applicant.lastname}}</a></td>' +
		'<td><a href="/settings/association/{{applicant.association.id}}">{{applicant.association.name}}</a></td>' +
		'<td>{{ label }}</td>' +
		'<td>{{ winesort.name }}</td>' +
		'<td>{{ vintage }}</td>' +
		'<td class="text-center">' +
		'	{{#if winequality}}{{ winequality.abbr }}{{else}}-{{/if}}' +
		'</td>' +
		'<td class="text-center">{{ l10nFloat alcohol }}</td>' +
		'<td class="text-center">{{#if alcoholtot}}{{ l10nFloat alcoholtot }}{{else}}-{{/if}}</td>' +
		'<td class="text-center">{{ l10nFloat sugar }}</td>' +
		'{{#if show_rating1 }}<td class="text-center">{{#if rating1}}{{ l10nFloat rating1 }}{{else}}-{{/if}}</td>{{/if}}' +
		'{{#if show_rating2 }}<td class="text-center">{{#if rating2}}{{ l10nFloat rating2 }}{{else}}-{{/if}}</td>{{/if}}' +
		'{{#if show_kdb }}<td class="text-center {{#if edit_kdb}}edit-kdb{{/if}}">' +
		'    {{#if kdb}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>{{/if}}' +
		'{{#if show_excluded }}<td class="text-center {{#if edit_excluded}}edit-excluded{{/if}}">' +
		'    {{#if excluded}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>{{/if}}' +
		'{{#if show_sosi }}<td class="text-center {{#if edit_sosi}}edit-sosi{{/if}}">' +
		'    {{#if sosi}}' +
		'    <span class="glyphicon glyphicon-ok"></span>' +
		'    {{else}}' +
		'    -' +
		'    {{/if}}' +
		'</td>{{/if}}' +
		'{{#if show_chosen }}<td class="text-center {{#if canEditChosen}}edit-chosen{{/if}}">' +
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

	function handleAPIErrors(e) {
		console.error(e);
		var errorMsg = 'Die Änderung konnte nicht gespeichert werden.';
		if (e && e.responseJSON && e.responseJSON.error) {
			errorMsg += ' Fehler: ' + e.responseJSON.error;
		}
		Weinstein.Views.showError({
			text: errorMsg
		});
	}

	var WineView = Marionette.View.extend({
		tagName: 'tr',
		template: Handlebars.compile(WINE_TEMPLATE),
		_tableOptions: {},
		ui: {
			editKdb: '.edit-kdb',
			editExcluded: '.edit-excluded',
			editSosi: '.edit-sosi',
			editChosen: '.edit-chosen'
		},
		events: {
			'click @ui.editKdb': '_editKdb',
			'click @ui.editExcluded': '_editExcluded',
			'click @ui.editSosi': '_editSosi',
			'click @ui.editChosen': '_editChosen'
		},
		modelEvents: {
			'change': 'render'
		},
		initialize: function (options) {
			this._tableOptions = options.tableOptions;

			this._editKdb = _.debounce(this._editKdb, 1000, true);
		},

		/**
		 * @returns {string|undefined}
		 */
		_getAssociationUsername: function () {
			var applicant = this.model.get('applicant');
			if (applicant && applicant.association) {
				return applicant.association.wuser_username;
			}
			return undefined;
		},

		templateContext: function () {
			var ctx = this._tableOptions;
			ctx.canEditChosen = ctx.edit_chosen && (Weinstein.currentUser.isAdmin ||
				Weinstein.currentUser.username === Weinstein.currentCompetition.adminUsername ||
				Weinstein.currentUser.username === this._getAssociationUsername());
			return ctx;
		},
		_editKdb: function () {
			if (!this._tableOptions.edit_kdb) {
				return;
			}

			var oldState = this.model.get('kdb');
			Promise.resolve(this.model.save({
				kdb: !oldState
			})).catch(function (e) {
				handleAPIErrors(e);
				this.model.set('kdb', oldState);
			}.bind(this));
		},
		_editExcluded: function () {
			if (!this._tableOptions.edit_excluded) {
				return;
			}

			var oldState = this.model.get('excluded');
			Promise.resolve(this.model.save({
				excluded: !oldState
			})).catch(function (e) {
				handleAPIErrors(e);
				this.model.set('excluded', oldState);
			}.bind(this));
		},
		_editSosi: function () {
			if (!this._tableOptions.edit_sosi) {
				return;
			}

			var oldState = this.model.get('sosi');
			Promise.resolve(this.model.save({
				sosi: !oldState
			})).catch(function (e) {
				handleAPIErrors(e);
				this.model.set('sosi', oldState);
			}.bind(this));
		},
		_editChosen: function () {
			if (!this._tableOptions.edit_chosen) {
				return;
			}

			var oldState = this.model.get('chosen');
			Promise.resolve(this.model.save({
				chosen: !oldState
			})).catch(function (e) {
				handleAPIErrors(e);
				this.model.set('chosen', oldState);
			}.bind(this));
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
		ui: {
			loadMore: 'button.wine-load-more'
		},
		events: {
			'click @ui.loadMore': '_loadMore'
		},
		/**
		 * @param {object} options
		 */
		initialize: function (options) {
			this._wines = options.wines;
			this._tableOptions = options.tableOptions;
		},
		templateContext: function () {
			return this._tableOptions;
		},
		onRender: function () {
			var listView = new WineListView({
				el: this.$('#wine_list'),
				collection: this._wines,
				tableOptions: this._tableOptions
			});
			listView.render();
		},
		_loadMore: function () {
			this.getUI('loadMore').button('loading');
			var loading = this._wines.nextPage();

			if (typeof loading !== 'undefined') {
				loading.always(function () {
					this.getUI('loadMore').button('reset');
				}.bind(this));
			} else {
				this.getUI('loadMore').button('reset');
				// TODO: find a better solution than hiding
				this.getUI('loadMore').hide();
			}
		}
	});

	Weinstein.Views.WineView = WineView;

})(Weinstein, Marionette, Handlebars);

