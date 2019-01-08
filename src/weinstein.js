import $ from 'jquery'
import '@babel/polyfill'
import {init} from '@sentry/browser';
import 'bootstrap'
import 'select2/dist/css/select2.css'
import 'select2/dist/js/select2'
import 'select2/dist/js/i18n/de'

import './style/weinstein.less';

import {addGlobal} from './globals';
import './entertab'
import {TastersView} from './views/tasterview';
import {retastebutton} from './retastebutton';
import {setUser} from './user';
import {WineCollection} from './models/wine';
import {WinesView} from './views/wineview';

// TODO: refactor away from these globals
addGlobal('$', $);
addGlobal('TastersView', TastersView);
addGlobal('retastebutton', retastebutton);
addGlobal('WineCollection', WineCollection);
addGlobal('WinesView', WinesView);
addGlobal('setUser', setUser);

console.debug('Weinstein dependencies and scripts loaded');

window.wsinit = ({csrfToken, dsn, release, environment}) => {
    $(function () {
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': csrfToken}
        });
    });

    try {
        init({
            dsn,
            release,
            environment
        })
    } catch (e) {
        console.error('Could not initialize Sentry:', e)
    }

    console.info('Weinstein initialized');
}
