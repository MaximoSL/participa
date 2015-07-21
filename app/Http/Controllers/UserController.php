<?php

namespace MXAbierto\Participa\Http\Controllers;

use MXAbierto\Participa\Models\User;

class UserController extends AbstractController
{
    /**
     * Retrieve user by id and display user page.
     *
     * @param \MXAbierto\Participa\Models\User $user
     *
     * @return Illuminate\View\View
     */
    public function getIndex(User $user)
    {
        //Render view and return
        return view('user.index', [
            'user'       => $user,
            'page_id'    => 'user_profile',
            'page_title' => $user->fname.' '.substr($user->lname, 0, 1)."'s Profile",
        ]);
    }
}
