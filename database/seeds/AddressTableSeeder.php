<?php

use Illuminate\Database\Seeder;

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
class AddressTableSeeder extends Seeder {

	/**
	 * Insert new address into database
	 *
	 * @param int $zipcode
	 * @param string $city
	 * @param string $street
	 * @param string $nr
	 */
	public static function createAddress($zipcode, $city, $street, $nr): \App\MasterData\Address {
		return \App\MasterData\Address::create(array(
			'zipcode' => $zipcode,
			'city' => $city,
			'street' => $street,
			'nr' => $nr,
		));
	}

}
