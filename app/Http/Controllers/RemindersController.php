<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use MXAbierto\Participa\Models\User;

class RemindersController extends AbstractController
{
    /**
     * Display the password reminder view.
     *
     * @return Response
     */
    public function getRemind()
    {
        return view('password.remind', [
            'page_id'    => 'dashboard',
            'page_title' => 'Dashboard',
        ]);
    }

    /**
     * Send a reset link to the given user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postRemind(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject(trans('messages.resetemailtitle'));
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return redirect()->back()->with('status', trans($response));

            case Password::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans('messages.remindersent')]);
        }
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param string $token
     *
     * @return Response
     */
    public function getReset($token = null)
    {
        if (is_null($token)) {
            abort(404);
        }

        return view('password.reset', [
            'page_id'    => 'reset',
            'page_title' => 'Reset Password',
        ])->with('token', $token);
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        $this->validate($request, [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                return redirect()->route('home')->with('message', trans('messages.passresetsuccess'));

            default:
                return redirect()->back()
                            ->withInput($request->only('email'))
                            ->withErrors(['email' => trans($response)]);
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string                                      $password
     *
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = $password;

        $user->save();

        Auth::login($user);
    }

    public function getConfirmation()
    {
        return view('password.resend', [
            'page_id'    => 'dashboard',
            'page_title' => 'Resend confirmation email',
        ]);
    }

    /**
     * Handle a POST request to remind a user of their password.
     *
     * @return \Illuminate\Http\Response
     */
    public function postConfirmation()
    {
        // 3 error cases - user already confirmed, email does not exist, password not correct
        // (prevents people from brute-forcing email addresses to see who is registered)
        $email = Input::get('email');
        $password = Input::get('password');
        $user = User::where('email', $email)->first();

        if (!isset($user)) {
            return redirect()->route('verification.remind')->with('error', 'Ese correo no fue registrado.');
        }

        if (empty($user->token)) {
            return redirect()->route('auth.login')->with('error', 'El usuario ya estaba confirmado.');
        }

        if (!Hash::check($password, $user->password)) {
            return redirect()->route('verification.remind')->with('error', 'La contraseña para ese correo es incorrecta.');
        }

        $token = $user->token;
        $email = $user->email;
        $fname = $user->fname;

        //Send email to user for email account verification
        Mail::queue('email.signup', ['token' => $token], function (Message $message) use ($email, $fname) {
            $message->subject(trans('messages.confirmationtitle'));
            $message->from(trans('messages.emailfrom'), trans('messages.emailfromname'));
            $message->to($email);
        });

        return redirect()->route('auth.login')->with('message', trans('messages.confirmationresent'));
    }
}
