<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use MXAbierto\Participa\Models\UserMeta;

class ModalController extends AbstractController
{
    /**
     * Creates a new modal controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function seenAnnotationThanksModal()
    {
        $userId = Auth::user()->id;

        $userMeta = UserMeta::firstOrNew([
            'user_id'  => $userId,
            'meta_key' => UserMeta::TYPE_SEEN_ANNOTATION_THANKS, ]);

        $userMeta->meta_value = true;

        $userMeta->save();
    }

    public function getAnnotationThanksModal()
    {
        return view('modal.annotations.thanks');
    }
}
