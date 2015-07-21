<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use MXAbierto\Participa\Models\MadisonEvent;
use MXAbierto\Participa\Models\UserMeta;

class AccountController extends AbstractController
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
     * Allow user to edit their account.
     *
     * @return \Illuminate\Http\View
     */
    public function getEdit()
    {
        $user = Auth::user();

        return view('account.edit', [
            'page_id'    => 'edit_profile',
            'page_title' => 'Editar Perfil',
            'user'       => $user,
        ]);
    }

    /**
     * User's put request to update their profile.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function patchAccount()
    {
        $user = Auth::user();

        if (strlen(Input::get('password_1')) > 0 || strlen(Input::get('password_2')) > 0) {
            if (Input::get('password_1') !== Input::get('password_2')) {
                return redirect()->route('user.account')->with('error', 'Las contraseñas que has ingresado no coinciden.');
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
            return redirect()->route('user.account')->withInput()->withErrors($user->getErrors());
        }

        if (isset($verify)) {
            $meta = new UserMeta();
            $meta->meta_key = 'verify';
            $meta->meta_value = 'pending';
            $meta->user_id = $user->id;
            $meta->save();

            event(MadisonEvent::VERIFY_REQUEST_USER, $user);

            return redirect()->route('user.account')->with('success_message', 'Tu perfil ha sido actualizado')->with('message', 'Se ha solicitado su estado de verificación.');
        }

        return redirect()->route('user.account')->with('success_message', 'Tu perfil ha sido actualizado.');
    }
}
