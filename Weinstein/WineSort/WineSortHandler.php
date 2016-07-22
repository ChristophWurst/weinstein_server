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

namespace Weinstein\WineSort;

use App\WineSort;

class WineSortHandler {

    /**
     * Wine sort data provider
     * 
     * @var \Weinstein\WineSort\WineSortDataProvider 
     */
    private $dataProvider;

    /**
     * Constructor
     * 
     * @param \Weinstein\WineSort\WineSortDataProvider $dataProvider
     */
    public function __construct(WineSortDataProvider $dataProvider) {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Create a new wine sort
     * 
     * @param array $data
     * @return WineSort;
     */
    public function create(array $data) {
        $validator = new WineSortValidator($data);
        $validator->validateCreate();
        $wineSort = new WineSort($data);
        $wineSort->save();
        return $wineSort;
    }

    /**
     * Update the wine sort
     * 
     * @param WineSort $wineSort
     * @param array $data
     * @return WineSort
     */
    public function update(WineSort $wineSort, array $data) {
        $validator = new WineSortValidator($data, $wineSort);
        $validator->validateUpdate();
        return $wineSort->update($data);
    }

    /**
     * Delete the wine sort
     * 
     * @param WineSort $wineSort
     * @return WineSort
     */
    public function delete(WineSort $wineSort) {
        $wineSort->delete();
    }

}
