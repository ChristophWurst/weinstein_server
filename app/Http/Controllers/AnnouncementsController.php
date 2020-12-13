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

use App\Mail\Announcement;
use App\MasterData\Applicant;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use function pathinfo;

class AnnouncementsController extends BaseController
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
     * @return View
     */
    public function index()
    {
        $this->authorize('send-announcements');

        return $this->viewFactory->make('settings/announcements/form');
    }

    /**
     * @return Response
     */
    public function send(Request $request)
    {
        $this->authorize('send-announcements');

        $subject = $request->get('subject');
        $text = $request->get('text');

        if (empty($subject) || empty($text)) {
            return Redirect::route('settings.announcements')
                ->withInput();
        }

        $emails = Applicant::select('email')
            ->whereNotNull('email')
            ->get();
        foreach ($emails->pluck('email') as $recipient) {
            Mail::to($recipient)->send(new Announcement($subject, $text));
        }

        $request->session()->flash('announcement_sent', [$emails->count()]);

        return Redirect::route('settings.announcements');
    }
}
