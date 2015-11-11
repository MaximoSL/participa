<?php

namespace MXAbierto\Participa\Http\Controllers;

use GrahamCampbell\Binput\Facades\Binput;
use Illuminate\Support\Facades\Auth;
use MXAbierto\Participa\Models\User;

class NotificationsController extends AbstractController
{
    /**
     * Creates a new settings controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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
        $user = Auth::user();

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
        $user = Auth::user();

        //Grab notification array
        $notifications = Binput::get('notifications');

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
}
