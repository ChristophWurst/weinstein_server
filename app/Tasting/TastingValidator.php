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

namespace App\Tasting;

use App\Tasting\Commission;
use App\Tasting\TastingSession;
use App\Validation\Validator;
use Illuminate\Database\Eloquent\Model;

class TastingValidator extends Validator {

	/**
	 * Tasting session to generate rules for
	 * 
	 * @var TastingSession
	 */
	private $tastingSession = null;

	/**
	 * Commission to generate update rules for
	 * 
	 * @var Commission
	 */
	private $commission = null;

	/**
	 * Nr of commmissions to generate ruels for
	 * 
	 * @var int
	 */
	private $nrOfCommissions = null;

	/**
	 * Models class name
	 * 
	 * @var string
	 */
	protected $modelClass = TastingNumber::class;

	/**
	 * Get attributes names
	 * 
	 * @return array
	 */
	protected function getAttributeNames() {
		$names = array(
			'tastingnumber_id1' => 'Kostnummer A',
			'tastingnumber_id2' => 'Kostnummer B',
			'comment' => 'Kommentar',
			'comment-a' => 'Kommentar 1',
			'comment-b' => 'Kommentar 2',
		);

		foreach ($this->tastingSession->commissions as $commission) {
			foreach ($commission->tasters()->active()->get() as $taster) {
				$name = 'Koster ' . strtoupper($commission->side) . $taster->nr . ' (' . $taster->name . ')';
				$names[$commission->side . $taster->nr] = $name;
			}
		}

		return $names;
	}

	/**
	 * Get validation error messages
	 * 
	 * @return array
	 */
	protected function getErrorMessages() {
		return array(
			'tastingnumber_id1.unique' => 'Dateinummer 1 wurde bereits verkostet',
			'tastingnumber_id2.unique' => 'Dateinummer 2 wurde bereits verkostet'
		);
	}

	/**
	 * Get rules for creating a new tasting
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function getCreateRules(array $data) {
		$rules = array();

		$commissionCount = 1;
		foreach ($this->tastingSession->commissions as $commission) {
			if (($this->nrOfCommissions < 2) && ($commissionCount > 1)) {
				break;
			}
			foreach ($commission->tasters()->active()->get() as $taster) {
				$rules[$commission->side . $taster->nr] = 'required|integer|between:10,50';
			}
			$commissionCount++;
		}

		$rules['tastingnumber_id1'] = 'required|numeric|exists:tastingnumber,id';
		$rules['comment-a'] = 'sometimes|max:100';
		//the unique rule prevents that a wine is tasted if anybody has tasted it before
		$rules['tastingnumber_id1'] .= '|unique:tasting,tastingnumber_id';
		if (($this->tastingSession->commissions()->count() > 1) && ($this->nrOfCommissions > 1)) {
			$rules['tastingnumber_id2'] = $rules['tastingnumber_id1'];
			$rules['comment-b'] = 'sometimes|max:100';
		}

		return $rules;
	}

	/**
	 * Get rules for updating an existing tasting
	 * 
	 * @param array $data
	 * @param Model $model
	 * @return array
	 */
	protected function getUpdateRules(array $data, Model $model = null) {
		$rules = array();
		foreach ($this->commission->tasters as $taster) {
			$rules[$this->commission->side . $taster->nr] = 'required|integer|between:10,50';
		}
		$rules['tastingnumber_id'] = 'required|numeric|exists:tastingnumber,id';
		$rules['comment'] = 'sometimes|max:100';
		return $rules;
	}

	/**
	 * Set the tasting session to generate rules for
	 * 
	 * @param TastingSession $tastingSession
	 */
	public function setTastingSession(TastingSession $tastingSession) {
		$this->tastingSession = $tastingSession;
	}

	/**
	 * Set the commission to generate update rules for
	 * 
	 * @param Commission $commission
	 */
	public function setCommission(Commission $commission) {
		$this->commission = $commission;
	}

	/**
	 * Set number of commmissions to generate rules for
	 * 
	 * @param int $nr
	 */
	public function setNrOfCommissions($nr) {
		$this->nrOfCommissions = $nr;
	}

}
