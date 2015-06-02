<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use MXAbierto\Participa\Models\User;

class UserController extends AbstractController
{
    /**
     * Creates a new user controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     *	API PUT Route to update a user's notification settings.
     *
     *	@param User $user
     *
     *	@return Response::json
     *
     * @todo There has to be a more efficient way to do this... We should probably only send changes from Angular.  We can also separate the array matching into helper functions
     */
    public function putNotifications(User $user)
    {
        if (Auth::user()->id !== $user->id) {
            return Response::json($this->growlMessage('No tienes permisos para editar la configuración de notificaciones de este usuario', 'error'));
        }

        //Grab notification array
        $notifications = Input::get('notifications');

        //Retrieve valid notification events
        $validNotifications = Notification::getUserNotifications();
        $events = array_keys($validNotifications);

        //Loop through each notification
        foreach ($notifications as $notification) {

            //Ensure this is a known user event.
            if (!in_array($notification['event'], $events)) {
                return Response::json($this->growlMessage('No ha sido posible guardar ajustes.  Evento desconocido: '.$notification['event'], 'error'));
            }

            //Grab this notification from the database
            $model = Notification::where('user_id', '=', $user->id)->where('event', '=', $notification['event'])->first();

            //If we don't want that notification (and it exists), delete it
            if ($notification['selected'] === false) {
                if (isset($model)) {
                    $model->delete();
                }
            } else {
                //If the entry doesn't already exist, create it.
                    //Otherwise, ignore ( there was no change )
                if (!isset($model)) {
                    $model = new Notification();
                    $model->user_id = $user->id;
                    $model->event = $notification['event'];
                    $model->type = 'email';

                    $model->save();
                }
            }
        }

        return Response::json($this->growlMessage('Configuraciones guardadas con éxito.', 'success'));
    }

    /**
     *	API GET Route to get viable User notifications and notification statuses for current user.
     *
     *	@param User $user
     *
     *	@return Response::json
     *
     *	@todo I'm sure this can be simplified...
     */
    public function getNotifications(User $user)
    {
        if (Auth::user()->id !== $user->id) {
            return Response::json($this->growlMessage('No tienes permisos para ver la configuración de notificaciones de este usuario', 'error'), 401);
        }

        //Retrieve all valid user notifications as associative array (event => description)
        $validNotifications = Notification::getUserNotifications();

        //Filter out event keys
        $events = array_keys($validNotifications);

        //Retreive all User Events for the current user
        $currentNotifications = Notification::select('event')->where('user_id', '=', $user->id)->whereIn('event', $events)->get();

        //Filter out event names from selected notifications
        $currentNotifications = $currentNotifications->toArray();
        $selectedEvents = [];
        foreach ($currentNotifications as $notification) {
            array_push($selectedEvents, $notification['event']);
        }

        //Build array of notifications and their selected status
        $toReturn = [];
        foreach ($validNotifications as $event => $description) {
            $notification = [];
            $notification['event'] = $event;
            $notification['description'] = $description;
            $notification['selected'] = in_array($event, $selectedEvents) ? true : false;

            array_push($toReturn, $notification);
        }

        return Response::json($toReturn);
    }

    /**
     *	Notification Preference Page.
     *
     *	@param User $user
     *
     *	@return Illuminate\View\View
     */
    public function editNotifications(User $user)
    {
        return view('single');
    }

    /**
     *	Api route to edit user's email.
     *
     * @param  \MXAbierto\Participa\Models\User  $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editEmail(User $user)
    {
        //Check authorization
        if (Auth::user()->id !== $user->id) {
            return Response::json($this->growlMessage('No estás autorizado a cambiar el email del usuario', 'error'));
        }

        $user->email = Input::get('email');
        $user->password = Input::get('password');

        if (! $user->save()) {
            $errors = $user->getErrors();
            $messages = [];

            foreach ($errors->all() as $error) {
                array_push($messages, $error);
            }

            return response()->json($this->growlMessage($messages, 'error'), 500);
        }

        return response()->json($this->growlMessage('Email guardado exitosamente.  Gracias.', 'success'), 200);
    }

    /**
     * Retrieve user by id and display user page
     *
     * @param  \MXAbierto\Participa\Models\User $user
     *
     * @return Illuminate\View\View
     */
    public function getIndex(User $user)
    {
        //Set data array
        $data = [
            'user'            => $user,
            'page_id'         => 'user_profile',
            'page_title'      => $user->fname.' '.substr($user->lname, 0, 1)."'s Profile",
        ];

        //Render view and return
        return view('user.index', $data);
    }

    /**
     * Allow user to edit their profile
     *
     * @param  \MXAbierto\Participa\Models\User $user
     *
     * @return Illuminate\View|View
     */
    public function getEdit(User $user)
    {
        if (Auth::user()->id != $user->id) {
            return redirect()->back()->with('error', 'No tienes acceso a ese perfil.');
        }

        return view('user.edit.index', [
            'page_id'    => 'edit_profile',
            'page_title' => 'Editar Perfil',
            'user'       => $user,
        ]);
    }

    /**
     *	putEdit.
     *
     *	User's put request to update their profile
     *
     *	@param  \MXAbierto\Participa\Models\User $user
     *
     *	@return Illuminate\Http\RedirectResponse
     */
    public function putEdit(User $user)
    {
        if (Auth::user()->id != $user->id) {
            return Redirect::back()->with('error', 'No tienes acceso a ese perfil.');
        }

        if (strlen(Input::get('password_1')) > 0 || strlen(Input::get('password_2')) > 0) {
            if (Input::get('password_1') !== Input::get('password_2')) {
                return Redirect::to('user/edit/'.$user->id)->with('error', 'Las contraseñas que has ingresado no coinciden.');
            } else {
                $user->password = Input::get('password_1');
            }
        }

        $verify = Input::get('verify');

        $user->email = Input::get('email');
        $user->fname = Input::get('fname');
        $user->lname = Input::get('lname');
        $user->url = Input::get('url');
        $user->phone = Input::get('phone');
        $user->verify = $verify;

        // Don't allow oauth logins to update the user's data anymore,
        // since they've set values within Madison.
        $user->oauth_update = false;
        if (!$user->save()) {
            return Redirect::to('user/edit/'.$user->id)->withInput()->withErrors($user->getErrors());
        }

        if (isset($verify)) {
            $meta = new UserMeta();
            $meta->meta_key = 'verify';
            $meta->meta_value = 'pending';
            $meta->user_id = $user->id;
            $meta->save();

            Event::fire(MadisonEvent::VERIFY_REQUEST_USER, $user);

            return Redirect::back()->with('success_message', 'Tu perfil ha sido actualizado')->with('message', 'Se ha solicitado su estado de verificación.');
        }

        return Redirect::back()->with('success_message', 'Tu perfil ha sido actualizado.');
    }

    /**
     *	putIndex.
     *
     *	Returns 404 Response
     *
     *	@param $id
     *
     *	@return Response
     *
     *	@todo Remove route and method
     */
    public function putIndex($id = null)
    {
        return Response::error('404');
    }

    /**
     *	postIndex.
     *
     *	Returns 404 Response
     *
     *	@param $id
     *
     *	@return Response
     *
     *	@todo remove route and method
     */
    public function postIndex($id = null)
    {
        return Response::error('404');
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
        echo $token;
        $user = User::where('token', $token)->first();

        if (isset($user)) {
            $user->token = '';
            $user->save();

            Auth::login($user);

            return Redirect::route('home')->with('success_message', 'Tu email ha sido verificado y ahora estás conectado.  Bienvenida/o '.$user->fname);
        } else {
            return Redirect::route('user/login')->with('error', 'El enlace de verificación no es válido.');
        }
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
             return Redirect::to((string) $url);
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
            return Redirect::to((string) $url);
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
            return Redirect::to((string) $url);
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

                    return Redirect::route('home')->with('success_message', 'Conectado con dirección de email '.$existing_user->email);
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

        return Redirect::route('home')->with('success_message', $message);
    }
}
