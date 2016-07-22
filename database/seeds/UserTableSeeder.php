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
class UserTableSeeder extends Seeder {

    /**
     * Insert new user into database
     * 
     * @param string $username
     * @param string $password
     * @param boolean $admin
     * @return User
     */
    public static function createUser($username, $password, $admin) {
        return User::create(array(
            'username' => $username,
            'password' => $password,
            'admin' => $admin,
        ));
    }

    /**
     * Run user table seeder
     */
    public function run() {
        //delete all users
        DB::table('wuser')->delete();

        //users
        $this->createUser('user1', 'user1!?', false);
        $this->createUser('user2', 'user2!?', false);
        $this->createUser('user3', 'user3!?', false);
        $this->createUser('user4', 'user4!?', false);
        $this->createUser('user5', 'user5!?', false);

        //admins
        $this->createUser('admin1', 'admin1!?', true);
        $this->createUser('admin2', 'admin2!?', true);
        $this->createUser('admin3', 'admin3!?', true);
        $this->createUser('admin4', 'admin4!?', true);
        $this->createUser('admin5', 'admin5!?', true);

        //sample user
        $this->createUser('christoph', 'test', true);
    }

}
