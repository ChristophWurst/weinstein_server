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

function tasterForm(config) {
	var tastersUrl = config['tastersUrl'];
	var updateUrl = config['updateUrl'];
	var commId = config['commissionId'];
	var button = $('#' + config['button']);
	var input = $('#' + config['input']);
	var list = $('#' + config['list']);
	var error_elem = $('#' + config['error_elem']);
	var error_list = $('#' + config['error_list']);
	var locked = config['locked'];

	var renderTasters = function (tasters) {
		list.empty();
		$.each(tasters, function (idx, taster) {
			var li = $('<li>');
			if (!taster['active']) {
				var s = $('<s>');
				s.html(taster['name']);
				li.append(s);
				list.append(li);
			} else {
				li.html(taster['name']);
				list.append(li);
			}
			list.append(li);
		});
	}

	var renderErrors = function (errors) {
		error_list.empty();
		$.each(errors, function (idx, error) {
			error_list.append($('<li>').append(error));
		});
		error_elem.removeClass('hidden');
	}

	var loadSuccess = function (data, status, xhr) {
		renderTasters(data);
		if (!locked) {
			button.removeClass('disabled');
		}
	};

	var loadError = function (xhr, status, error) {
		alert('Fehler beim Laden der Verkoster');
	};

	var loadTasters = function () {
		//do ajax call
		$.ajax(tastersUrl, {
			'error': loadError,
			'success': loadSuccess,
			'type': 'GET'
		});
	};

	var updateSuccess = function (data, status, xhr) {
		if (xhr.status == 200) {
			if (data['valid']) {
				renderTasters(data['tasters']);
			} else {
				renderErrors(data['errors']);
			}
		}
	};

	var updateError = function (xhr, status, error) {
		console.log('error');
	};

	var updateComplete = function (xhr, status) {
		input.val("");
		button.removeClass('disabled');
		input.removeClass('disabled');
		input.focus();
	};

	button.click(function (event) {
		button.addClass('disabled');
		input.addClass('disabled');
		error_elem.addClass('hidden');

		var data = {
			'commission_id': commId,
			'name': input.val()
		};

		//do ajax call
		$.ajax(updateUrl, {
			'complete': updateComplete,
			'error': updateError,
			'success': updateSuccess,
			'data': data,
			'type': 'POST'
		});
	});

	//load tasters
	button.addClass('disabled');
	loadTasters();
}