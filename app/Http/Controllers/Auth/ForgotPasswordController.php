<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\MasterData\User;
use Exception;
use function back;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use function is_null;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validateUsername(Request $request)
    {
        $this->validate($request, ['username' => 'required|min:4|max:80|alpha_dash']);
    }

    public function sendResetLinkUsername(Request $request)
    {
        $this->validateUsername($request);

        /** @var User $user */
        $user = User::find($request->get('username'));
        if (is_null($user)) {
            return back()->with('status', 'Benutzer existiert nicht');
        }
        try {
            // See if we can retrieve an email without an error
            $user->getEmailForPasswordReset();
        } catch (Exception $e) {
            return back()->with('status', 'Keine E-Mail-Adresse hinterlegt');
        }

        $response = $this->broker()->sendResetLink(
            $request->only('username')
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
