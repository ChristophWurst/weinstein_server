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

use App\MasterData\Download;
use Illuminate\Contracts\View\Factory;

class DownloadsController extends BaseController
{
    /** @var Factory */
    private $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function index()
    {
        return $this->viewFactory->make('download/index', [
                'downloads' => Download::all(),
        ]);
    }
}
