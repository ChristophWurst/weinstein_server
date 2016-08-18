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

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Weinstein\Exception\ValidationException;
use WineSortHandler;
use WineSort;

class WineSortController extends BaseController {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		//register filters
		$this->middleware('auth');
		$this->middleware('@filterAdmin');
	}

	/**
	 * Display a listing of all sorts
	 *
	 * @return Response
	 */
	public function index() {
		$this->authorize('list-winesorts');

		return View::make('settings/winesorts/index')->with('sorts', WineSort::all());
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
			WineSortHandler::create(Input::all());
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
			WineSortHandler::update($wineSort, Input::all());
		} catch (ValidationException $ve) {
			return Redirect::route('settings.winesorts/edit', ['winesort' => $wineSort->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.winesorts');
	}

}
