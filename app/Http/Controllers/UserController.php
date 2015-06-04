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
        //Render view and return
        return view('user.index', [
            'user'            => $user,
            'page_id'         => 'user_profile',
            'page_title'      => $user->fname.' '.substr($user->lname, 0, 1)."'s Profile",
        ]);
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

        return view('user.edit', [
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
}
