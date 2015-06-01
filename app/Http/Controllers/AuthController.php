<?php

namespace MXAbierto\Participa\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use MXAbierto\Participa\Events\UserHasRegisteredEvent;
use MXAbierto\Participa\Models\User;

class AuthController extends AbstractController
{
    /**
     * Displays the login form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Illuminate\View\View
     */
    public function getLogin(Request $request)
    {
        $previous_page = $request->old('previous_page', URL::previous());

        return view('auth.login', [
            'page_id'          => 'login',
            'page_title'       => 'Log In',
            'previous_page'    => $previous_page,
        ]);
    }

   /**
    * Handles POST requests for users logging in.
    *
    * @param  \Illuminate\Http\Request $request
    *
    * @return Illuminate\Http\RedirectResponse
    */
   public function postLogin(Request $request)
   {
       //Rules for login form submission
       $this->validate($request, [
           'email'    => 'required|email',
           'password' => 'required',
        ]);

       // Retrieve POST values
       $email = $request->input('email');
       $password = $request->input('password');
       $previous_page = $request->input('previous_page');
       $user_details = $request->all();

       // Check that the user account exists
       $user = User::where('email', $email)->first();

       if (!$user) {
           return redirect()->route('auth.login')->with('error', 'Ese email no existe.');
       }

       // If the user's token field isn't blank, he/she hasn't confirmed their account via email
       if ($user->token != '') {
           return redirect()->route('auth.login')->with('error', 'Por favor, haz click en el enlace enviado a tu email para verificar la cuenta.');
       }

       //Attempt to log user in
       $credentials = ['email' => $email, 'password' => $password];

       if (!Auth::attempt($credentials, $request->has('remember'))) {
           return redirect()->route('auth.login')->with('error', 'Datos incorrectos')->withInput(['previous_page' => $previous_page]);
       }

       Auth::user()->last_login = Carbon::now();
       Auth::user()->save();

       if ($previous_page) {
           return redirect()->to($previous_page)->with('message', 'Has ingresado exitosamente.');
       } else {
           return redirect()->route('docs')->with('message', 'Has ingresado exitosamente.');
       }
   }

   /**
    * Returns signup page form.
    *
    * @return Illuminate\View\View
    */
   public function getSignup()
   {
       $data = [
           'page_id'        => 'signup',
           'page_title'     => 'Registro a Participa',
       ];

       return view('auth.signup', $data);
   }

   /**
    * Handles POST requests for users signing up.
    * Fires UserHasRegisteredEvent.
    *
    * @param  \Illuminate\Http\Request $request
    *
    * @return Illuminate\Http\RedirectResponse
    */
   public function postSignup(Request $request)
   {
       //Retrieve POST values
       $email = $request->input('email');
       $password = $request->input('password');
       $fname = $request->input('fname');
       $lname = $request->input('lname');

       //Create user token for email verification
       $token = str_random();

       //Create new user
       $user = new User();
       $user->email = $email;
       $user->password = $password;
       $user->fname = $fname;
       $user->lname = $lname;
       $user->token = $token;

       if (!$user->save()) {
           return redirect()->route('auth.signup')->withInput()->withErrors($user->getErrors());
       }

       event(new UserHasRegisteredEvent($user));

       return redirect()->route('auth.login')->with('message', trans('messages.confirmationresent'));
   }
}
