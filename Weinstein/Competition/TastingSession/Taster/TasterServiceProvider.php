<?php

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

namespace Weinstein\Competition\TastingSession\Taster;

use App;
use Illuminate\Support\ServiceProvider;

class TasterServiceProvider extends ServiceProvider {

	/**
	 * Register taster services
	 */
	public function register() {
		$this->app->bind('TasterHandler', function() {
			$dataProvider = App::make('Weinstein\Competition\TastingSession\Taster\TasterDataProvider');
			return new TasterHandler($dataProvider);
		});
	}

}
