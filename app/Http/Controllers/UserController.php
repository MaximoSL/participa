<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class UserController extends AbstractController
{
    /**
     * Displays the .
     *
     * Returns the login page view
     *
     * @param void
     *
     * @return Illuminate\View\View
     */
    public function getLogin()
    {
        $previous_page = Input::old('previous_page', URL::previous());

        return view('login.index', [
            'page_id'          => 'login',
            'page_title'       => 'Log In',
            'previous_page'    => $previous_page,
        ]);
    }

    /**
    *
    * Handles POST requests for users logging in
    *
    * @param void
    *
    * @return Illuminate\Http\RedirectResponse
    */
   public function postLogin()
   {
       //Retrieve POST values
       $email = Input::get('email');
       $password = Input::get('password');
       $previous_page = Input::get('previous_page');
       $remember = Input::get('remember');
       $user_details = Input::all();

       //Rules for login form submission
       $rules = ['email' => 'required', 'password' => 'required'];
       $validation = Validator::make($user_details, $rules);

       //Validate input against rules
       if ($validation->fails()) {
           return redirect()->route('user/login')->withInput()->withErrors($validation);
       }

       //Check that the user account exists
       $user = User::where('email', $email)->first();

       if (!isset($user)) {
           return Redirect::route('user/login')->with('error', 'Ese email no existe.');
       }

       //If the user's token field isn't blank, he/she hasn't confirmed their account via email
       if ($user->token != '') {
           return Redirect::route('user/login')->with('error', 'Por favor, haz click en el enlace enviado a tu email para verificar la cuenta.');
       }

       //Attempt to log user in
       $credentials = ['email' => $email, 'password' => $password];

       if (Auth::attempt($credentials, ($remember == 'true') ? true : false)) {
           Auth::user()->last_login = new DateTime();
           Auth::user()->save();
           if (isset($previous_page)) {
               return Redirect::to($previous_page)->with('message', 'Has ingresado exitosamente.');
           } else {
               return Redirect::route('docs')->with('message', 'Has ingresado exitosamente.');
           }
       } else {
           return Redirect::route('user/login')->with('error', 'Datos incorrectos')->withInput(['previous_page' => $previous_page]);
       }
   }
}
