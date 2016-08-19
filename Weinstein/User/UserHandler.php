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

namespace Weinstein\User;

use Weinstein\User\UserDataProvider;
use Weinstein\User\UserValidator;
use ActivityLogger;
use App;
use Auth;
use App\MasterData\User;

class UserHandler {

    /**
     * App\MasterData\User data provider
     * 
     * @var \Weinstein\User\UserDataProvider
     */
    private $dataProvider;

    public function __construct(UserDataProvider $dataProvider) {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Create a new user
     * 
     * @param array $data
     * @return App\MasterData\User
     */
    public function create(array $data) {
        $userValidator = new UserValidator($data);
        $userValidator->validateCreate();
        $user = new App\MasterData\User($data);
        $user->save();
        ActivityLogger::log('Benutzer [' . $data['username'] . '] erstellt');
        return $user;
    }

    /**
     * Update an exising user
     * 
     * @param App\MasterData\User $user
     * @param array $data
     * @return App\MasterData\User
     */
    public function update(User $user, array $data) {
        //do not change password if it was left blank
        if (isset($data['password']) && $data['password'] === '') {
            unset($data['password']);
        }

        $userValidator = new UserValidator($data, $user);
        $userValidator->validateUpdate();
        //prevent admin from removing its own admin privileges
        if (Auth::user()->username == $user->username) {
            unset($data['admin']);
        }
        $user->update($data);
        ActivityLogger::log('Benutzer [' . $user->username . '] bearbeitet');
        return $user;
    }

    /**
     * Delete an user
     * 
     * @param App\MasterData\User $user
     */
    public function delete(User $user) {
        //prevent user from deleting his own account
        if ($user->username == Auth::user()->username) {
            App::abort(500);
        }
        $username = $user->username;
        $user->delete();
        ActivityLogger::log('Benutzer [' . $username . '] gel&ouml;scht');
    }

    /**
     * Get given users users
     * 
     * @param App\MasterData\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersUsers(User $user) {
        if ($user->admin) {
            return $this->dataProvider->getAllUsers();
        } else {
            return $this->dataProvider->getUsersForUser($user);
        }
    }

    /**
     * Check if given loginUser administrates user
     * 
     * @param App\MasterData\User $admin
     * @param App\MasterData\User $user
     * @return boolean
     */
    public function isAdmin(User $admin, App\MasterData\User $user) {
        if ($admin->username === $user->username) {
            return true;
        } elseif ($admin->admin) {
            return true;
        }
        return false;
    }

}
