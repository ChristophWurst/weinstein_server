<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\MasterData\User;
use function back;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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

        $user = User::find($request->get('username'));
        $email = $user->getEmailForPasswordReset();
        if (is_null($email)) {
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
