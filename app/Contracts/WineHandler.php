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

use App\MasterData\Competition;
use App\MasterData\User;
use App\Wine;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;

interface WineHandler {

	/**
	 * @param array $data
	 * @param Competition $competition
	 * @return Wine
	 */
	public function create(array $data, Competition $competition);

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @throws ValidationException
	 * @return Wine
	 */
	public function update(Wine $wine, array $data);

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @throws ValidationException
	 */
	public function updateKdb(Wine $wine, array $data);

	/**
	 * @param UploadedFile $file
	 * @param Competition $competition
	 */
	public function importKdb(UploadedFile $file, Competition $competition);

	/**
	 * @param UploadedFile $file
	 * @param Competition $competition
	 */
	public function importExcluded(UploadedFile $file, Competition $competition);

	/**
	public function updateSosi(Wine $wine, array $data);

	/**
	 * @param UploadedFile $file
	 * @param Competition $competition
	 */
	public function importSosi(UploadedFile $file, Competition $competition);

	/**
	 * @param Wine $wine
	 * @param array $data
	 */
	public function updateChosen(Wine $wine, array $data);

	/**
	 * @param UploadedFile $file
	 * @param Competition $competition
	 */
	public function importChosen(UploadedFile $file, Competition $competition);

	/**
	 * @param Wine $wine
	 */
	public function delete(Wine $wine);

	/**
	 * Get all wines of given competition for given user
	 *
	 * @param User $user
	 * @param Competition $competition
	 * @return Paginator
	 */
	public function getUsersWines(User $user, Competition $competition);
}
