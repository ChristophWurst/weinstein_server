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
 */

namespace App\Wine;

use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Validation\Validator;
use App\Wine;
use Illuminate\Database\Eloquent\Model;

class WineValidator extends Validator
{
    /**
     * Competition to generate rules for.
     *
     * @var Competition
     */
    private $competition = null;

    /**
     * @var User
     */
    private $user = null;

    protected $modelClass = Wine::class;

    /**
     * Get attributes names.
     *
     * @return array
     */
    protected function getAttributeNames()
    {
        return [
            'nr' => 'Dateinummer',
            'applicant_id' => 'Betrieb',
            'label' => 'Marke',
            'winesort_id' => 'Sorte',
            'winequality_id' => 'Qualit&auml;tsstufe',
            'vintage' => 'Jahrgang',
            'alcohol' => 'Alkohol',
            'alcoholtot' => 'Alkohol gesamt',
            'sugar' => 'Zucker',
            'approvalnr' => 'Pr&uuml;fnummer',
        ];
    }

    /**
     * Get custom validation error messages.
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        return [
            'association_id.exists' => 'Verein existiert nicht oder kann nicht automatisch zugeordnet werden',
        ];
    }

    private function requiresApprovalNr()
    {
        return ! $this->competition->administrates($this->user)
            && $this->competition->competitionState->id === CompetitionState::STATE_ENROLLMENT;
    }

    /**
     * Get create rules.
     *
     * @param array $data
     * @return array
     */
    protected function getCreateRules(array $data)
    {
        return [
            'nr' => $this->competition->administrates($this->user) ? 'required|integer|min:1'
                .'|unique:wine,nr,'
                .'NULL,id,'
                .'competition_id,'.$this->competition->id : '',
            'applicant_id' => 'required|exists:applicant,id',
            'label' => 'max:25',
            'winesort_id' => 'required|exists:winesort,id',
            'winequality_id' => 'Exists:winequality,id',
            'vintage' => 'required|integer|between:2000,2030',
            'alcohol' => 'required|numeric|between:0.1,30.0',
            'alcoholtot' => 'numeric|between:0.1,99.9',
            'sugar' => 'required|numeric|between:0.1,300.0',
            'approvalnr' => 'sometimes|alpha_num|max:20',
        ];
    }

    /**
     * Get update rules.
     *
     * @param array $data
     * @param Model $model
     * @return array
     */
    protected function getUpdateRules(array $data, Model $model = null)
    {
        $nrUnchanged = isset($data['nr']) && $data['nr'] !== '' && ! is_null($data['nr']) && $data['nr'] == $model->nr;

        return [
            'nr' => $nrUnchanged || ! $this->competition->administrates($this->user) ? '' : ('required|integer|min:1'
                .'|unique:wine,nr,'
                .'NULL,id,'
                .'competition_id,'.$this->competition->id),
            'applicant_id' => 'required|exists:applicant,id',
            'label' => 'max:25',
            'winesort_id' => 'required|exists:winesort,id',
            'winequality_id' => 'Exists:winequality,id',
            'vintage' => 'required|integer|between:2000,2030',
            'alcohol' => 'required|numeric|between:0.1,30.0',
            'alcoholtot' => 'numeric|between:0.1,99.9',
            'sugar' => 'required|numeric|between:0.1,300.0',
            'approvalnr' => 'sometimes|alpha_num|max:20',
        ];
    }

    /**
     * Set Competition.
     *
     * @param Competition $competition
     */
    public function setCompetition(Competition $competition)
    {
        $this->competition = $competition;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
