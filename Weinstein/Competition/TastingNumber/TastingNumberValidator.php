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

namespace Weinstein\Competition\TastingNumber;

use App\MasterData\Competition;
use Illuminate\Database\Eloquent\Model;
use Weinstein\Support\Validation\Validator;

class TastingNumberValidator extends Validator {

	/**
	 * @var Competition
	 */
	private $competition = null;

	/**
	 * Models class name
	 * 
	 * @var string
	 */
	protected $modelClass = 'TastingNumber';

	/**
	 * Get attributes names
	 * 
	 * @return array
	 */
	protected function getAttributeNames() {
		return array(
		    'wine_nr' => 'Dateinummer',
		    'nr' => 'Kostnummer',
		);
	}

	/**
	 * Get rules for creating a new tasting number
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function getCreateRules(array $data) {
		return array(
		    'wine_nr' => 'required|integer'
		    . '|tastingnumber_wine_exists:' . $this->competition->id
		    . '|tastingnumber_wine_unique:' . $this->competition->id,
		    'nr' => 'required|integer|min:1'
		    . '|tastingnumber_nr_unique:' . $this->competition->id,
		);
	}

	/**
	 * Get rules for updating an existing tasting number
	 * 
	 * @param array $data
	 * @param Model $model
	 * @return array
	 */
	protected function getUpdateRules(array $data, Model $model = null) {
		return array(
		    'wine_nr' => 'required|integer',
		    'nr' => 'required|integer|min:1',
		);
	}

	/**
	 * Set competition to generate rules for
	 * 
	 * @param Competition $competition
	 */
	public function setCompetition(Competition $competition) {
		$this->competition = $competition;
	}

}
