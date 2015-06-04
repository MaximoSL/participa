<?php

namespace MXAbierto\Participa\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use MXAbierto\Participa\Http\Controllers\AbstractController;

/**
 * 	Controller for admin dashboard.
 */
class SettingsController extends AbstractController
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

    /**
     *	Settings page.
     */
    public function getSettings()
    {
        $data = [
            'page_id'        => 'settings',
            'page_title'     => 'Settings',
        ];

        $user = Auth::user();

        if (!$user->can('admin_manage_settings')) {
            return Redirect::to('/participa/dashboard')->with('message', trans('messages.nopermission'));
        }

        return view('dashboard.settings', $data);
    }

    public function postSettings()
    {
        $user = Auth::user();

        if (!$user->can('admin_manage_settings')) {
            return Redirect::to('/participa/dashboard')->with('message', trans('messages.nopermission'));
        }

        $adminEmail = Input::get('contact-email');

        $adminContact = User::where('email', '$adminEmail');

        if (!isset($adminContact)) {
            return Redirect::back()->with('error', trans('messages.noadminaccountwithemail'));
        }
    }
}
