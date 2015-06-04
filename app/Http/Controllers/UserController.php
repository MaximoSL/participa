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
}
