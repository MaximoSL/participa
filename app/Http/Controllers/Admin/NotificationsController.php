<?php

namespace MXAbierto\Participa\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use MXAbierto\Participa\Http\Controllers\AbstractController;
use MXAbierto\Participa\Models\Notification;

/**
 * 	Controller for admin dashboard.
 */
class NotificationsController extends AbstractController
{
    /**
     * Creates a new dashboard controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //Filter to ensure user is signed in has an admin role
        $this->beforeFilter('admin');

        //Run csrf filter before all posts
        $this->beforeFilter('csrf', ['on' => 'post']);
    }

    public function getNotifications()
    {
        $notifications = Notification::where('user_id', '=', Auth::user()->id)->get();
        $validNotifications = Notification::getValidNotifications();

        $selectedNotifications = [];
        foreach ($notifications as $n) {
            $selectedNotifications[] = $n->event;
        }

        return view('dashboard.notifications', compact('selectedNotifications', 'validNotifications'));
    }

    public function postNotifications()
    {
        $notifications = Input::get('notifications');

        if (!is_array($notifications)) {
            return Redirect::to('/participa/dashboard/notifications');
        }

        Notification::where('user_id', '=', Auth::user()->id)
                    ->whereIn('event', array_keys(Notification::getValidNotifications()))
                    ->delete();

        foreach ($notifications as $n) {
            Notification::addNotificationForUser($n, Auth::user()->id);
        }

        return Redirect::to('/participa/dashboard/notifications')->with('success_message', trans('messages.updatednotif'));
    }

}
