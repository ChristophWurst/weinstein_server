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

namespace App\Http\Controllers;

use App\Contracts\MasterDataStore;
use App\Exceptions\ValidationException;
use App\MasterData\WineSort;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class WineSortController extends BaseController {

	/** @var MasterDataStore */
	private $masterDataStore;

	public function __construct(MasterDataStore $masterDataStore) {
		$this->masterDataStore = $masterDataStore;
	}

	/**
	 * Display a listing of all sorts
	 *
	 * @return Response
	 */
	public function index() {
		$this->authorize('list-winesorts');

		return View::make('settings/winesorts/index')->with('sorts', $this->masterDataStore->getWineSorts());
	}

	/**
	 * Show the form for creating a new sort
	 *
	 * @return Response
	 */
	public function create() {
		$this->authorize('create-winesort');

		return View::make('settings/winesorts/form');
	}

	/**
	 * Store a newly created sort in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$this->authorize('create-winesort');

		try {
			$data = Input::all();
			$this->masterDataStore->createWineSort($data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.winesorts/create')
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.winesorts');
	}

	/**
	 * Show the form for editing the specified sort
	 *
	 * @param WineSort $wineSort
	 * @return Response
	 */
	public function edit(WineSort $wineSort) {
		$this->authorize('update-winesort', $wineSort);

		return View::make('settings/winesorts/form')->with([
				'data' => $wineSort,
		]);
	}

	/**
	 * Update the specified sort in storage
	 *
	 * @param WineSort $wineSort
	 * @return Response
	 */
	public function update(WineSort $wineSort) {
		$this->authorize('update-winesort', $wineSort);

		try {
			$data = Input::all();
			$this->masterDataStore->updateWineSort($wineSort, $data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.winesorts/edit', ['winesort' => $wineSort->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.winesorts');
	}

}
