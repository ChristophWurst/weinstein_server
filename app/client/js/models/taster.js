/* global Backbone */

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

(function (Weinstein, Backbone) {
	'use strict';

	Weinstein.Models = Weinstein.Models || {};

	Weinstein.Models.Taster = Backbone.Model.extend({
		defaults: {
			active: true,
			name: ''
		}
	});

	Weinstein.Models.TasterCollection = Backbone.Collection.extend({
		model: Weinstein.Models.Taster
	});

})(Weinstein, Backbone);
