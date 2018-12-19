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

import $ from 'jquery';
import {Collection, Model} from 'backbone';

export const Wine = Model.extend({
    defaults: {
        label: ''
    }
});

export const WineCollection = Collection.extend({
    model: Wine,
    _nextPageUrl: null,
    parse: function (data) {
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }
        this._nextPageUrl = data.next_page_url;
        return data.data;
    },
    nextPage: function () {
        if (!this._nextPageUrl) {
            return;
        }
        return $.ajax(this._nextPageUrl, {
            method: 'GET'
        }).then(function (data) {
            var models = this.parse(data);
            this.add(models);
        }.bind(this)).fail(function () {
            console.log('could not load next wine page');
        });
    },
    comparator: 'nr'
});
