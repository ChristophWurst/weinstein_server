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

use App\Contracts\TastingCatalogueHandler;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\TastingCatalogue\CatalogueHandler;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;

class CatalogueNumberController extends BaseController {

	/** @var ViewFactory */
	private $viewFactory;

	/** @var Redirector */
	private $redirector;

	/** @var CatalogueHandler */
	private $catalogueHandler;

	/**
	 * @param ViewFactory $viewFactory
	 * @param Redirector $redirector
	 * @param TastingCatalogueHandler $catalogueHandler
	 */
	public function __construct(ViewFactory $viewFactory, Redirector $redirector, TastingCatalogueHandler $catalogueHandler) {
		$this->viewFactory = $viewFactory;
		$this->catalogueHandler = $catalogueHandler;
		$this->redirector = $redirector;
	}

	/**
	 * @param Competition $competition
	 * @return View
	 */
	public function import(Competition $competition): View {
		$this->authorize('import-catalogue-numbers', $competition);
		return $this->viewFactory->make('competition/cataloguenumbers/import');
	}

	/**
	 * @param Request $request
	 * @param Competition $competition
	 * @return RedirectResponse
	 */
	public function store(Request $request, Competition $competition): RedirectResponse {
		$this->authorize('import-catalogue-numbers', $competition);
		$file = $request->file('xlsfile');

		if (is_null($file)) {
			return $this->redirector->route('cataloguenumbers.import', [
					'competition' => $competition,
			]);
		}

		try {
			$importedRows = $this->catalogueHandler->importCatalogueNumbers($file, $competition);
		} catch (ValidationException $ex) {
			return $this->redirector->route('cataloguenumbers.import', [
					'competition' => $competition,
				])->withErrors($ex->getErrors());
		}
		Session::flash('rowsImported', $importedRows);
		return $this->redirector->route('enrollment.wines', [
				'competition' => $competition,
		]);
	}

}
