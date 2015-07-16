<?php

namespace MXAbierto\Participa\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use MXAbierto\Participa\Events\UserHasRegisteredEvent;
use MXAbierto\Participa\Models\User;

class AuthController extends AbstractController
{
    /**
     * The social login scopes.
     *
     * @return string[]
     */
    protected $scopes = [
        'facebook' => ['email', 'public_profile'],
        'twitter'  => [],
        'linkedin' => ['r_basicprofile', 'r_emailaddress'],
    ];

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
    * Get social connect.
    *
    * @param  string $provider
    *
    * @return \Illuminate\Http\RedirectResponse
    */
    public function getConnect($provider)
    {
        try {
            return Socialite::driver($provider)->scopes(array_get($this->scopes, $provider))->redirect();
        } catch (\Exception $e){
            abort(404);
        }
    }


   /**
    * Get social connect callback.
    *
    * @param  string $provider
    *
    * @return \Illuminate\Http\RedirectResponse
    */
    public function getCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (\Exception $e){
            return redirect()->route('auth.login')->with('error', 'La conexión ha fallado intenta nuevamente por favor.');
        }

        $parts = explode(" ", $user->getName());
        $lastname = array_pop($parts);
        $firstname = implode(" ", $parts);

        $userInfo = [
            'fname'        => $firstname,
            'lname'        => $lastname,
            'email'        => $user->getEmail(),
            'oauth_vendor' => $provider,
            'oauth_id'     => $user->getId(),
        ];

        return $this->oauthLogin($userInfo);
    }

   /**
    *	oauthLogin.
    *
    * Use OAuth data to login user.  Create account if necessary.
    *
    *	@param array $userInfo
    *
    *	@return Illuminate\Http\RedirectResponse
    *
    *	@todo Should this be moved to the User model?
    */
   protected function oauthLogin($userInfo)
   {
       // See if we already have a matching user in the system
       $user = User::where('oauth_vendor', $userInfo['oauth_vendor'])
           ->where('oauth_id', $userInfo['oauth_id'])->first();

       if (!$user) {
           // Make sure this user doesn't already exist in the system.
           if (isset($userInfo['email'])) {
               if ($existingUser = User::where('email', $userInfo['email'])->first()) {
                   Auth::login($existingUser);

                   return redirect()->route('home')->with('success_message', 'Conectado con dirección de email '.$existingUser->email);
               }
           }

           // Create a new user since we don't have one.
           $user = new User();
           $user->oauth_vendor = $userInfo['oauth_vendor'];
           $user->oauth_id = $userInfo['oauth_id'];
           $user->oauth_update = true;

           $new_user = true;
       }

       // Now that we have a user for sure, update the user and log them in.
       $user->fname = $userInfo['fname'];
       $user->lname = $userInfo['lname'];
       if (isset($userInfo['email'])) {
           $user->email = $userInfo['email'];
       }

       // If the user is new, or if we are allowed to update the user via oauth.
       // Note: The oauth_update flag is turned to off the first time the user
       // edits their account within Madison, locking in their info.
       if (isset($new_user) || (isset($user->oauth_update) && $user->oauth_update == true)) {
           if (!$user->save()) {
               Log::error('Unable to save user: ', $userInfo);
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
