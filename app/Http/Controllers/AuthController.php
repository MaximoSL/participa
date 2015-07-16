<?php

namespace MXAbierto\Participa\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use MXAbierto\Participa\Events\UserHasRegisteredEvent;
use MXAbierto\Participa\Models\User;

class AuthController extends AbstractController
{
    /**
     * Creates a new auth contoller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Displays the login form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Illuminate\View\View
     */
    public function getLogin(Request $request)
    {
        if ($request->ajax()) {
            return view('auth.api.login');
        }

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
        $validation = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        //Validate input against rules
        if ($validation->fails()) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'errors' => $validation->errors()->all()]);
            }

            return redirect()->route('auth.login')->withInput()->withErrors($validation->errors()->all());
        }

       // Retrieve POST values
       $error = [];
       $email = $request->input('email');
       $password = $request->input('password');
       $previous_page = $request->input('previous_page');
       $user_details = $request->all();

       // Check that the user account exists
       $user = User::where('email', $email)->first();

       if (!$user) {
           $errors[] = 'Ese email no existe.';
       } else {
           // If the user's token field isn't blank, he/she hasn't confirmed their account via email
           if ($user->token != '') {
               $errors[] = 'Por favor, haz click en el enlace enviado a tu email para verificar la cuenta.';
           }
       }

       if (!empty($errors)) {
           if ($request->ajax()) {
               return response()->json(['status' => 'error', 'errors' => $errors]);
           }

           return redirect()->route('auth.login')->withInput()->with('error', $errors[0]);
       }

       //Attempt to log user in
       $credentials = ['email' => $email, 'password' => $password];

       if (!Auth::attempt($credentials, $request->has('remember'))) {
           if ($request->ajax()) {
               return response()->json(['status' => 'error', 'errors' => ['Datos incorrectos']]);
           }

           return redirect()->route('auth.login')->with('error', 'Datos incorrectos')->withInput(['previous_page' => $previous_page]);
       }

       Auth::user()->last_login = Carbon::now();
       Auth::user()->save();

       if ($request->ajax()) {
           return response()->json(['status' => 'ok', 'errors' => [], 'message' => 'Has ingresado exitosamente.']);
       }

       if ($previous_page) {
           return redirect()->to($previous_page)->with('message', 'Has ingresado exitosamente.');
       } else {
           return redirect()->route('docs')->with('message', 'Has ingresado exitosamente.');
       }
   }

   /**
    * Returns signup page form.
    *
    * @param  \Illuminate\Http\Request $request
    *
    * @return Illuminate\View\View
    */
   public function getSignup(Request $request)
   {
       if ($request->ajax()) {
           return view('auth.api.signup');
       }

       return view('auth.signup', [
           'page_id'        => 'signup',
           'page_title'     => 'Registro a Participa',
       ]);
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

           if ($request->ajax()) {
               return response()->json(['status' => 'error', 'errors' => $user->getErrors() ]);
           }

           return redirect()->route('auth.signup')->withInput()->withErrors($user->getErrors());
       }

       event(new UserHasRegisteredEvent($user));

       if ($request->ajax()) {
           return response()->json(['status' => 'ok', 'errors' => [], 'message' => trans('messages.confirmationresent')]);
       }

       return redirect()->route('auth.login')->with('message', trans('messages.confirmationresent'));
   }

   /**
    * Logouts out a user from it's account.
    *
    * @return \Illuminate\Http\RedirectResponse
    */
   public function getLogout()
   {
       Auth::logout();    //Logout the current user
       session()->flush(); //delete the session

       return redirect()->route('home')->with('message', 'Has salido exitosamente.');
   }

   /**
    * 	getVerify.
    *
    *	Handles GET requests for email verifications
    *
    *	@param string $token
    *
    *	@return Illuminate\Http\RedirectRequest
    */
   public function getVerify($token)
   {
       $user = User::where('token', $token)->first();

       if (!$user) {
           return redirect()->route('auth.login')->with('error', 'El enlace de verificación no es válido.');
       }

       $user->token = '';
       $user->save();

       Auth::login($user);

       return redirect()->route('home')->with('success_message', 'Tu email ha sido verificado y ahora estás conectado.  Bienvenida/o '.$user->fname);
   }

   /**
    * getFacebookLogin.
    *
    *	Handles OAuth communication with Facebook for signup / login
    *		Calls $this->getAuthorizationUri() if the oauth code is passed via Input
    *		Otherwise calls $fb->getAuthorizationUri()
    *
    *	@param void
    *
    *	@return Illuminate\Http\RedirectResponse || $this->oauthLogin($user_info)
    *
    *	@todo clean up this doc block
    */
   public function getFacebookLogin()
   {

       // get data from input
       $code = Input::get('code');

       // get fb service
       $fb = OAuth::consumer('Facebook');

       // check if code is valid

       // if code is provided get user data and sign in
       if (!empty($code)) {

           // This was a callback request from facebook, get the token
           $token = $fb->requestAccessToken($code);

           // Send a request with it
           $result = json_decode($fb->request('/me'), true);

           // Remap the $result to something that matches our schema.
           $user_info = [
               'fname'        => $result['first_name'],
               'lname'        => $result['last_name'],
               'email'        => $result['email'],
               'oauth_vendor' => 'facebook',
               'oauth_id'     => $result['id'],
           ];

           return $this->oauthLogin($user_info);
       }
       // if not ask for permission first
       else {
           // get fb authorization
           $url = $fb->getAuthorizationUri();

           // return to facebook login url
            return redirect()->to((string) $url);
       }
   }

   /**
    * getFacebookLogin.
    *
    *	Handles OAuth communication with Twitter for signup / login
    *		Calls $this->oauthLogin() if the oauth code is passed via Input
    *		Otherwise calls $tw->requestRequestToken()
    *
    *	@param void
    *
    *	@return Illuminate\Http\RedirectResponse || $this->oauthLogin($user_info)
    *
    *	@todo clean up this doc block
    */
   public function getTwitterLogin()
   {

       // get data from input
       $token = Input::get('oauth_token');
       $verify = Input::get('oauth_verifier');

       // get twitter service
       $tw = OAuth::consumer('Twitter');

       // check if code is valid

       // if code is provided get user data and sign in
       if (!empty($token) && !empty($verify)) {

       // This was a callback request from twitter, get the token
       $token = $tw->requestAccessToken($token, $verify);

       // Send a request with it
       $result = json_decode($tw->request('account/verify_credentials.json'), true);

           $user_info = [
                   'fname'        => $result['name'],
                   'lname'        => '-',
                   'oauth_vendor' => 'twitter',
                   'oauth_id'     => $result['id'],
               ];

           return $this->oauthLogin($user_info);
       }
       // if not ask for permission first
       else {
           // get request token
           $reqToken = $tw->requestRequestToken();

           // get Authorization Uri sending the request token
           $url = $tw->getAuthorizationUri(['oauth_token' => $reqToken->getRequestToken()]);

           // return to twitter login url
           return redirect()->to((string) $url);
       }
   }

   /**
    * getFacebookLogin.
    *
    *	Handles OAuth communication with Facebook for signup / login
    *		Calls $this->oauthLogin() if the oauth code is passed via Input
    *		Otherwise calls $linkedinService->getAuthorizationUri()
    *
    *	@param void
    *
    *	@return Illuminate\Http\RedirectResponse || $this->oauthLogin($user_info)
    *
    *	@todo clean up this doc block
    */
   public function getLinkedinLogin()
   {

       // get data from input
       $code = Input::get('code');

       $linkedinService = OAuth::consumer('Linkedin');

       if (!empty($code)) {

               // retrieve the CSRF state parameter
               $state = isset($_GET['state']) ? $_GET['state'] : null;

               // This was a callback request from linkedin, get the token
               $token = $linkedinService->requestAccessToken($_GET['code'], $state);

           // Send a request with it. Please note that XML is the default format.
           $result = json_decode($linkedinService->request('/people/~:(id,first-name,last-name,email-address)?format=json'), true);

                   // Remap the $result to something that matches our schema.
                   $user_info = [
                       'fname'        => $result['firstName'],
                       'lname'        => $result['lastName'],
                       'email'        => $result['emailAddress'],
                       'oauth_vendor' => 'linkedin',
                       'oauth_id'     => $result['id'],
                   ];

           return $this->oauthLogin($user_info);
       }// if not ask for permission first
       else {
           // get linkedinService authorization
           $url = $linkedinService->getAuthorizationUri();

           // return to linkedin login url
           return redirect()->to((string) $url);
       }
   }

   /**
    *	oauthLogin.
    *
    * Use OAuth data to login user.  Create account if necessary.
    *
    *	@param array $user_info
    *
    *	@return Illuminate\Http\RedirectResponse
    *
    *	@todo Should this be moved to the User model?
    */
   public function oauthLogin($user_info)
   {
       // See if we already have a matching user in the system
       $user = User::where('oauth_vendor', $user_info['oauth_vendor'])
           ->where('oauth_id', $user_info['oauth_id'])->first();

       if (!isset($user)) {

           // Make sure this user doesn't already exist in the system.
           if (isset($user_info['email'])) {
               $existing_user = User::where('email', $user_info['email'])->first();

               if (isset($existing_user)) {
                   Auth::login($existing_user);

                   return redirect()->route('home')->with('success_message', 'Conectado con dirección de email '.$existing_user->email);
               }
           }

           // Create a new user since we don't have one.
           $user = new User();
           $user->oauth_vendor = $user_info['oauth_vendor'];
           $user->oauth_id = $user_info['oauth_id'];
           $user->oauth_update = true;

           $new_user = true;
       }

       // Now that we have a user for sure, update the user and log them in.
       $user->fname = $user_info['fname'];
       $user->lname = $user_info['lname'];
       if (isset($user_info['email'])) {
           $user->email = $user_info['email'];
       }

       // If the user is new, or if we are allowed to update the user via oauth.
       // Note: The oauth_update flag is turned to off the first time the user
       // edits their account within Madison, locking in their info.
       if (isset($new_user) || (isset($user->oauth_update) && $user->oauth_update == true)) {
           if (!$user->save()) {
               Log::error('Unable to save user: ', $user_info);
           }
       }

       if ($user instanceof User) {
           Auth::login($user);
       } else {
           Log::error('Tratando de autenticar usuario de tipo incorrecto', $user->toArray());
       }

       if (isset($new_user)) {
           $message = 'Bienvenido '.$user->fname;
       } else {
           $message = 'Benvenido de nuevo, '.$user->fname;
       }

       return redirect()->route('home')->with('success_message', $message);
   }
}
