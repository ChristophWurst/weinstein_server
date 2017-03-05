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

namespace App\Contracts;

use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use Illuminate\Http\UploadedFile;

interface TastingCatalogueHandler {

	/**
	 * Import assigned catalogue numbers of all chosen wines
	 *
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @throws ValidationException if data is invalid or incomplete (all wines have to be assigned a number)
	 * @return int number of read lines
	 */
	public function importCatalogueNumbers(UploadedFile $file, Competition $competition): int;
}
