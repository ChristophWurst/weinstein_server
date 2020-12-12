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

namespace App\Http\Controllers;

use App\Contracts\WineHandler;
use App\MasterData\Competition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class CatalogueController extends BaseController
{
    /**
     * Download address catalogue.
     *
     * @param Competition $competition
     * @return \Illuminate\Http\Response
     */
    public function addressCatalogue(Competition $competition)
    {
        $this->authorize('create-catalogue');

        $we = new AddressCatalogueExport($competition->addressCatalogue);
        $filename = 'Adresskatalog.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::download($we->asExcel(), $filename, $headers);
    }

    /**
     * Download web catalogue.
     *
     * @param Competition $competition
     * @return \Illuminate\Http\Response
     */
    public function webCatalogue(Competition $competition)
    {
        $this->authorize('create-catalogue');

        $wines = $competition
            ->wine_details()
            ->where('chosen', '=', true)
            ->with('applicant', 'applicant.address', 'applicant.association', 'winequality', 'winesort'
            )
            ->get();
        $we = new WebCatalogueExport($wines);
        $filename = 'Webkatalog.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::download($we->asExcel(), $filename, $headers);
    }

    /**
     * Download tasting catalogue.
     *
     * @param Competition $competition
     * @return \Illuminate\Http\Response
     */
    public function tastingCatalogue(Competition $competition, WineHandler $wineHandler)
    {
        $this->authorize('create-tasting-catalogue', $competition);

        $user = Auth::user();
        if ($user->isAdmin()) {
            $wines = $competition
                ->wine_details()
                ->Chosen()
                ->get();
            $we = new AdminTastingCatalogueExport($wines);
        } else {
            $wines = $competition
                ->wine_details()
                ->where([
                    ['chosen', '=', true],
                    ['applicant_username', '=', $user->username],
                ])
                ->orWhere([
                    ['chosen', '=', true],
                    ['association_username', '=', $user->username],
                ])
                ->orderBy('catalogue_number')
                ->get();
            $we = new TastingCatalogueExport($wines);
        }

        $filename = 'Kostkatalog.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::download($we->asExcel(), $filename, $headers);
    }
}
