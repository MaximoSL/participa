<?php

namespace MXAbierto\Participa\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use MXAbierto\Participa\Http\Controllers\AbstractController;
use MXAbierto\Participa\Models\Notification;

/**
 * 	Controller for admin dashboard.
 */
class NotificationsController extends AbstractController
{
    /**
     * Gets the available notifications form.
     *
     * @return \Illuminate\Http\Response
     */
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
            return Redirect::route('dashboard.notifications');
        }

        Notification::where('user_id', '=', Auth::user()->id)
                    ->whereIn('event', array_keys(Notification::getValidNotifications()))
                    ->delete();

        foreach ($notifications as $n) {
            Notification::addNotificationForUser($n, Auth::user()->id);
        }

        return Redirect::route('dashboard.notifications')->with('success_message', trans('messages.updatednotif'));
    }
}
