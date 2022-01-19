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

namespace App\Database\Repositories;

use App\MasterData\Address;
use App\MasterData\Applicant;
use App\MasterData\User;
use Illuminate\Database\Eloquent\Collection;

class ApplicantRepository
{
    /**
     * @return Collection
     */
    public function findAll()
    {
        return Applicant::all();
    }

    /**
     * @param User $user
     * @return Collection
     */
    public function findForUser(User $user)
    {
        $direct = $user->applicants()->get();
        $indirect = $user->associationApplicants()->get();

        $all = $direct->merge($indirect->all());
        $all->sortBy('id');

        return $all;
    }

    /**
     * @param array $data
     * @return Applicant
     */
    public function create(array $data)
    {
        $applicant = new Applicant($data);
        $address = new Address($data);
        $address->save();
        $applicant->address()->associate($address);
        $applicant->save();

        return $applicant;
    }

    /**
     * @param Applicant $applicant
     * @param array $data
     * @return Applicant
     */
    public function update(Applicant $applicant, array $data)
    {
        $applicant->update($data);
        $applicant->address->update($data);

        return $applicant;
    }

    public function delete(Applicant $applicant)
    {
        $applicant->delete();
    }
}
