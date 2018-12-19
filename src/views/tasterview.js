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
import {CollectionView, View} from 'backbone.marionette';

import {TasterCollection} from '../models/taster'
import TasterTemplate from '../templates/taster.hb'
import TastersTemplate from '../templates/tasters.hb'

export const TasterListItemView = View.extend({
    tagName: 'li',
    template: TasterTemplate,
    editing: false,
    ui: {
        text: 'span',
        input: 'input',
        disable: 'span.disable'
    },
    events: {
        'dblclick @ui.text': '_onDoubleClick',
        'keyup @ui.input': '_onInputKeyUp',
        'focusout @ui.input': '_onInputFocusOut',
        'click @ui.disable': '_onDisableTaster'
    },
    templateContext: function () {
        return {
            editing: this.editing
        };
    },
    initialize: function (options) {
        this.locked = options.locked;
    },
    _onDoubleClick: function () {
        if (this.locked) {
            // Ignore
            return;
        }
        this.editing = true;
        this.render();
        this.getUI('input').focus();
    },
    _onInputKeyUp: function (event) {
        event.preventDefault();

        if (event.keyCode === 27) {
            // ESC
            this.editing = false;
            this.render();
        }
        if (event.keyCode === 13) {
            // Enter
            this.model.save({
                name: this.getUI('input').val()
            }, {
                wait: true,
                success: _.bind(this._onUpdateSuccess, this),
                error: _.bind(this._onUpdateError, this)
            });
        }
    },
    _onInputFocusOut: function () {
        this.editing = false;
        this.render();
    },
    _onDisableTaster: function () {
        this.model.save({
            active: !this.model.get('active')
        }, {
            wait: true,
            success: _.bind(this._onUpdateSuccess, this),
            error: _.bind(this._onUpdateError, this)
        });
    },
    _onUpdateSuccess: function () {
        this.editing = false;
        this.render();
    },
    _onUpdateError: function () {
        this.editing = false;
        this.render();
        alert('Fehler beim Speichern der Änderungen');
    }
});

export const TasterListView = CollectionView.extend({
    tagName: 'ol',
    locked: false,
    childView: TasterListItemView,
    childViewOptions: function () {
        return {
            locked: this.locked
        };
    },
    initialize: function (options) {
        this.locked = options.locked;
    }
});

export const TastersView = View.extend({
    template: TastersTemplate,
    commissionId: undefined,
    regions: {
        tasters: {
            el: 'ol',
            replaceElement: true
        }
    },
    ui: {
        newTasterName: 'input.new-taster-name',
        addTasterBtn: 'button.add-taster-btn'
    },
    events: {
        'keyup @ui.newTasterName': '_onNewTasterNameKeyPress',
        'click @ui.addTasterBtn': '_onAddNewTaster'
    },
    childViewEvents: {
        'taster:update': '_onUpdateTaster'
    },
    templateContext: function () {
        return {
            side: this.side.toUpperCase(),
            locked: this.locked
        };
    },
    initialize: function (options) {
        this.collection = options.collection || new TasterCollection();
        this.collection.url = options.url;
        this.commissionId = options.commissionId;
        this.side = options.side || '';
        this.locked = options.locked || false;
    },
    onRender: function () {
        this.showChildView('tasters', new TasterListView({
            collection: this.collection,
            commissionId: this.commissionId
        }));
        this.collection.fetch({
            data: {
                commission_id: this.commissionId
            }
        });
    },
    _onNewTasterNameKeyPress: function (event) {
        if (event.keyCode === 13) {
            this._onAddNewTaster(event);
        }
    },
    _onAddNewTaster: function (event) {
        event.preventDefault();

        var input = this.getUI('newTasterName');
        var btn = this.getUI('addTasterBtn');

        input.prop('disabled', true);
        btn.prop('disabled', true);

        this.collection.create({
            name: input.val(),
            commission_id: this.commissionId
        }, {
            wait: true,
            complete: this._onCreateComplete,
            context: this
        });
    },
    _onCreateComplete: function () {
        var input = this.getUI('newTasterName');
        var btn = this.getUI('addTasterBtn');

        input.prop('disabled', false);
        btn.prop('disabled', false);

        input.val('');
        input.focus();
    }
});