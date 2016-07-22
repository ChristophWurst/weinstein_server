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

function wineList(config) {
	var list = $(config['list']);
	var idElemClass = config['idElem'];
	var elemClass = config['elem_class']
	var loadUrl = config['loadUrl'];
	var updateUrl = config['updateUrl'];
	var wines = [];
	var state = [];
	var loaded = false;
	var target = config['target'];

	var showLoadingGif = function () {
		$.each(wines, function (idx, wine) {
			$(wine).children(elemClass).children('span').addClass('hidden');
			$(wine).children(elemClass).addClass('loading');
		});
	}

	var hideLoadingGif = function () {
		$.each(wines, function (idx, wine) {
			$(wine).children(elemClass).children('span').removeClass('hidden');
			$(wine).children(elemClass).removeClass('loading');
		});
	}

	var handleHoverIn = function (id, wine) {
		if (!loaded) {
			//do nothing if data has not been loaded yet
			return;
		}
		var w = wine.children(elemClass);
		if (state[id]) {
			w.html($('<span>').addClass('glyphicon glyphicon-remove'));
		} else {
			w.html($('<span>').addClass('glyphicon glyphicon-ok'));
		}
	};

	var handleHoverOut = function (id, wine) {
		if (!loaded) {
			//do nothing if data has not been loaded yet
			return;
		}
		var w = wine.children(elemClass);
		if (state[id]) {
			w.html($('<span>').addClass('glyphicon glyphicon-ok'));
		} else {
			w.html('-');
		}
	};

	var updateError = function () {
		alert('Fehler beim Ändern von ' + target);
		hideLoadingGif();
	};

	var handleClick = function (id, wine) {
		if (!loaded) {
			//do nothing if data has not been loaded yet
			return;
		}
		//do not allow hover, click event while updating
		loaded = false;

		showLoadingGif();
		var url = $(wine).children(updateUrl).html();
		var data = {};
		if (state[id]) {
			data['value'] = 0;
		} else {
			data['value'] = 1;
		}
		$.ajax(url, {
			'data': data,
			'success': loadSuccess,
			'error': updateError,
			'type': 'POST'
		});
	};

	var init = function () {
		list.children().each(function () {
			var id = $(this).children(idElemClass).html();
			if ($(this).children('.association_admin').text() === 'n') {
				// simply ignore this line
				return;
			}
			var wine = wines[id] = $(this);
			wine.addClass('wine_edit');

			var icon = wine.children(elemClass);

			//set all event handlers
			icon.click(function () {
				handleClick(id, wine);
			});
			icon.hover(function () {
				handleHoverIn(id, wine);
			}, function () {
				handleHoverOut(id, wine);
			});
		});
		showLoadingGif();
		load();
	};

	var loadSuccess = function (data, status, xhr) {
		if (data['error']) {
			alert(data['error']);
		}
		//set default html
		$.each(wines, function (idx, wine) {
			$(wine).children(elemClass).html('-');
		});
		//reset state
		$.each(state, function (idx, elem) {
			state[idx] = false;
		});
		//load data
		$.each(data['wines'], function (idx, wine) {
			if (wines[wine]) {
				state[wine] = true;
				wines[wine].children(elemClass).html($('<span>').addClass('glyphicon glyphicon-ok'));
			}
		});

		//remove loading gif
		hideLoadingGif();
		loaded = true;
	};

	var loadError = function (xhr, status, error) {
		var data = JSON.parse(xhr.responseText);
		if (data['error']) {
			alert(data['error']);
		} else {
			alert('Fehler beim Laden von ' + target);
		}
	};

	var load = function () {
		//get all special wines from server
		$.ajax(loadUrl, {
			'success': loadSuccess,
			'error': loadError,
			'type': 'GET'
		});
	};

	init();
}