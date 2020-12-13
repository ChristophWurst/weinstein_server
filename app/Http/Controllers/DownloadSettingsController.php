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
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use function pathinfo;
use function storage_path;

class DownloadSettingsController extends BaseController
{
    /** @var AuthManager */
    private $auth;

    /** @var Factory */
    private $viewFactory;

    /**
     * @param AuthManager $auth
     * @param Factory $viewFactory
     */
    public function __construct(AuthManager $auth, Factory $viewFactory)
    {
        $this->auth = $auth;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Display a listing of all downloads the user is permitted to see.
     *
     * @return View
     */
    public function index()
    {
        $this->authorize('manage-downloads');

        /** @var User $user */
        $user = $this->auth->user();
        $downloads = Download::all();

        return $this->viewFactory->make('settings/download/index', [
                'downloads' => $downloads,
        ]);
    }

    /**
     * Show the form for creating a new download.
     *
     * @return View
     */
    public function create()
    {
        $this->authorize('manage-downloads');

        return $this->viewFactory->make('settings/download/form');
    }

    /**
     * Store a newly created download in storage.
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-downloads');

        if (! $request->hasFile('file')) {
            return Redirect::route('settings.downloads/create');
        }

        if (empty($request->name)) {
            $name = $request->file('file')->getClientOriginalName();
        } else {
            $name = $request->name;
            if (empty(pathinfo($name)['extension'])) {
                $name .= '.'.$request->file('file')->getClientOriginalExtension();
            }
        }
        $path = $request->file('file')->store('downloads');

        $download = new Download();
        $download->name = $name;
        $download->path = $path;
        $download->save();
        $request->session()->flash('download_created', [$download->name, $download->path]);

        return Redirect::route('settings.downloads');
    }

    /**
     * Display the specified download.
     *
     * @param Download $download
     */
    public function show(Download $download)
    {
        return \response()->download(storage_path('app/'.$download->path), $download->name);
    }

    public function delete(Download $download)
    {
        $this->authorize('manage-downloads', $download);

        return $this->viewFactory->make('settings/download/delete',
            [
                'download' => $download,
            ]);
    }

    public function destroy(Download $download, Request $request)
    {
        $this->authorize('manage-downloads', $download);

        if ($request->get('del') === 'Ja') {
            Storage::delete($download->path);
            $download->delete();
        }

        return Redirect::route('settings.downloads');
    }
}
