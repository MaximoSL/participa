<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class AuthController extends AbstractController
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
           return redirect()->route('user/login')->with('error', 'Ese email no existe.');
       }

       //If the user's token field isn't blank, he/she hasn't confirmed their account via email
       if ($user->token != '') {
           return redirect()->route('user/login')->with('error', 'Por favor, haz click en el enlace enviado a tu email para verificar la cuenta.');
       }

       //Attempt to log user in
       $credentials = ['email' => $email, 'password' => $password];

       if (Auth::attempt($credentials, ($remember == 'true') ? true : false)) {
           Auth::user()->last_login = new DateTime();
           Auth::user()->save();
           if (isset($previous_page)) {
               return redirect()->to($previous_page)->with('message', 'Has ingresado exitosamente.');
           } else {
               return redirect()->route('docs')->with('message', 'Has ingresado exitosamente.');
           }
       } else {
           return redirect()->route('user/login')->with('error', 'Datos incorrectos')->withInput(['previous_page' => $previous_page]);
       }
   }

   /**
    * 	getSignup.
    *
    *	Returns signup page view
    *
    *	@param void
    *
    *	@return Illuminate\View\View
    */
   public function getSignup()
   {
       $data = [
           'page_id'        => 'signup',
           'page_title'     => 'Registro a Participa',
       ];

       return view('login.signup', $data);
   }

   /**
    * Handles POST requests for users signing up natively through Madison
    * Fires MadisonEvent::NEW_USER_SIGNUP Event
    *
    * @param void
    *
    * @return Illuminate\Http\RedirectResponse
    */
   public function postSignup()
   {
       //Retrieve POST values
       $email = Input::get('email');
       $password = Input::get('password');
       $fname = Input::get('fname');
       $lname = Input::get('lname');

       //Create user token for email verification
       $token = str_random();

       //Create new user
       $user = new User();
       $user->email = $email;
       $user->password = $password;
       $user->fname = $fname;
       $user->lname = $lname;
       $user->token = $token;

       if (! $user->save()) {
           return redirect()->route('user/signup')->withInput()->withErrors($user->getErrors());
       }

       Event::fire(MadisonEvent::NEW_USER_SIGNUP, $user);

       //Send email to user for email account verification
       Mail::queue('email.signup', ['token' => $token], function ($message) use ($email, $fname) {
           $message->subject(trans('messages.confirmationtitle'));
           $message->from(trans('messages.emailfrom'), trans('messages.emailfromname'));
           $message->to($email); // Recipient address
       });

       return redirect()->route('user/login')->with('message', trans('messages.confirmationresent'));
   }
}
