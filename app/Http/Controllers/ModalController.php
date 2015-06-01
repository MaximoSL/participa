<?php

namespace MXAbierto\Participa\Http\Controllers;

class ModalController extends AbstractController
{
    public function seenAnnotationThanksModal()
    {
        if (!Auth::check()) {
            throw new Exception("Unauthorized");
        }

        $userId = Auth::user()->id;

        $userMeta = UserMeta::firstOrNew([
            'user_id'  => $userId,
            'meta_key' => UserMeta::TYPE_SEEN_ANNOTATION_THANKS, ]);

        $userMeta->meta_value = true;

        $userMeta->save();
    }

    public function getAnnotationThanksModal()
    {
        return View::make('modal.annotations.thanks');
    }
}
