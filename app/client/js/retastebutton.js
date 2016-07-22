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

/**
 * 
 * @param {type} config
 * @returns {undefined}
 */
function retastebutton(config) {
	var btnUrl = config.btnUrl;
	var translateUrl = config.translateUrl;
	var input = $(config.input);
	var btn = $(config.btn);
	var val = input.val();

	/**
	 * 
	 * @param {type} data
	 * @param {type} status
	 * @param {type} xhr
	 * @returns {undefined}
	 */
	var updateSuccess = function (data, status, xhr) {
		btn.removeClass('disabled');
		if (data.error) {
			btn.attr('href', btnUrl);
			btn.addClass('disabled');
		} else {
			btn.attr('href', btnUrl.replace(':tnr', data.tnr));
			btn.removeClass('disabled');
		}
	}

	/**
	 * 
	 * @param {type} xhr
	 * @param {type} status
	 * @param {type} error
	 * @returns {undefined}
	 */
	var updateError = function (xhr, status, error) {
		btn.addClass('disabled');
	}

	/**
	 * 
	 * @returns {undefined}
	 */
	var update = function () {
		var newVal = input.val();
		if (newVal !== val) {
			val = newVal;
		}
		if (newVal !== "") {
			var url = translateUrl.replace(':id', val);

			$.ajax(url, {
				'error': updateError,
				'success': updateSuccess,
				'type': 'GET'
			});
		} else {
			btn.addClass('disabled');
		}
	}

	input.val('');
	input.keyup(update);
	btn.addClass('disabled');
}