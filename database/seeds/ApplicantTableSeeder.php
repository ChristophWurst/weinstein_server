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
class ApplicantTableSeeder extends Seeder {

    /**
     * Insert new applicant into database
     * 
     * @param string $label
     * @param string $title
     * @param string $firstname
     * @param string $lastname
     * @param string $phone
     * @param string $fax
     * @param string $mobile
     * @param string $email
     * @param string $web
     * @param int $association
     * @param string|null $username
     * @param Address $address
     * @return Applicant
     */
    public static function createAppilcant($id, $label, $title, $firstname, $lastname, $phone, $fax, $mobile, $email, $web, $association, $username, Address $address) {
        $a = Applicant::create(array(
                    'id' => $id,
                    'label' => $label,
                    'title' => $title,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'phone' => $phone,
                    'fax' => $fax,
                    'mobile' => $mobile,
                    'email' => $email,
                    'web' => $web,
                    'association_id' => $association,
                    'wuser_username' => $username,
                    'address_id' => $address->id,
        ));
        return $a;
    }

    /**
     * Run applicant seeder
     */
    public function run() {
        //delete all existing applicants
        DB::table('applicant')->delete();

        for ($i = 1; $i <= 100; $i++) {
            if ($i % 7 === 0) {
                $username = 'user1';
            } elseif ($i % 11 === 0) {
                $username = 'user2';
            } elseif ($i % 15 === 0) {
                $username = 'admin1';
            } else {
                $username = null;
            }
            
            
            $address = AddressTableSeeder::createAddress(rand(1000, 9000), "city $i", "$i-street", "$i/$i");
            $this->createAppilcant($i * 10000, "applicant $i", "title $i", "first $i", "last $i", rand(10000000, 90000000), rand(10000000, 90000000), rand(10000000, 90000000), "$i@test.com", "www.test$i.com", $i % 20 + 1, $username, $address);
        }
    }

}
