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

/**
 *
 * @param {type} config
 * @returns {undefined}
 */
export const retastebutton = config => {
    const btnUrl = config.btnUrl;
    const translateUrl = config.translateUrl;
    const input = $(config.input);
    const btn = $(config.btn);
    let val = input.val();

    /**
     *
     * @param {type} data
     * @param {type} status
     * @param {type} xhr
     * @returns {undefined}
     */
    const updateSuccess = data => {
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
    const updateError = () => {
        btn.addClass('disabled');
    }

    /**
     *
     * @returns {undefined}
     */
    const update = () => {
        const newVal = input.val();
        if (newVal !== val) {
            val = newVal;
        }
        if (newVal !== "") {
            const url = translateUrl.replace(':id', val);

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