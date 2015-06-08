<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Input;
use MXAbierto\Participa\Models\Annotation;

/**
 * 	Controller for note actions.
 */
class AnnotationController extends AbstractController
{
    /**
     * GET note view.
     *
     * @return \Illuminate\View\View
     */
    public function getIndex($id = null)
    {
        //Return 404 if no id is passed
        if ($id == null) {
            abort(404, trans('messages.noteidnotfound'));
        }

        //Invalid note id
        $annotation = Annotation::find($id);

        if (!$annotation) {
            abort(404, ucfirst(strtolower(trans('messages.unable').' '.trans('messages.toretrieve').' '.trans('messages.the').' '.trans('messages.note'))));
        }

        $user = $annotation->user()->first();

        //Render view and return to user
        return view('annotation.index', [
            'page_id'             => 'Annotation',
            'page_title'          => 'View Annotation',
            'annotation'          => $annotation,
            'user'                => $user,
        ]);
    }
}
