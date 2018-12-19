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

import _ from 'underscore';
import {CompositeView, CollectionView, View} from 'backbone.marionette';

import {getCurrentCompetition} from '../competition';
import {getCurrentUser} from '../user'
import {showError} from './errormodalview'
import WineTemplate from '../templates/wine.hb';
import WineTableTemplate from '../templates/wine_table.hb';

function handleAPIErrors(e) {
    console.error(e);
    var errorMsg = 'Die Änderung konnte nicht gespeichert werden.';
    if (e && e.responseJSON && e.responseJSON.error) {
        errorMsg += ' Fehler: ' + e.responseJSON.error;
    }
    showError({
        text: errorMsg
    });
}

const WineView = View.extend({
    tagName: 'tr',
    template: WineTemplate,
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
        ctx.canEditChosen = ctx.edit_chosen && (getCurrentUser().isAdmin ||
            getCurrentUser().username === getCurrentCompetition().adminUsername ||
            getCurrentUser().username === this._getAssociationUsername());
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

export const WineListView = CollectionView.extend({
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

export const WinesView = CompositeView.extend({
    template: WineTableTemplate,
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
