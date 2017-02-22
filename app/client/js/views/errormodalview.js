/* global Weinstein, Marionette, Handlebars */

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

(function (Weinstein, $, _) {
	'use strict';

	Weinstein.Views = Weinstein.Views || {};

	function showError(options) {
		options = _.defaults(options, {
			text: ''
		});

		var $modal = $('#error-modal');
		$modal.find('.modal-body').text(options.text);
		$modal.modal('show');
	}

	Weinstein.Views.showError = showError;
})(Weinstein, $, _);
