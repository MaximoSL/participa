<?php

namespace MXAbierto\Participa\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use MXAbierto\Participa\Http\Controllers\AbstractController;
use MXAbierto\Participa\Models\Doc;

/**
 * 	Controller for admin dashboard.
 */
class DocumentsController extends AbstractController
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

    public function showDoc($id)
    {
        $doc = Doc::find($id);

        if (!$doc) {
            abort(404);
        }

        return view('documents.edit', [
            'page_id'        => 'edit_doc',
            'page_title'     => 'Edit '.$doc->title,
            'doc'            => $doc,
            // Just get the first content element.  We only have one, now.
            'contentItem' => $doc->content()->where('parent_id')->first(),
        ]);
    }

    /**
     * 	Document Creation/List or Document Edit Views.
     */
    public function getDocs($id = '')
    {
        $user = Auth::user();

        if (!$user->can('admin_manage_documents')) {
            return redirect()->route('dashboard')->with('message', trans('messages.nopermission'));
        }

        if ($id == '') {
            $docs = Doc::all();

            $data = [
                    'page_id'         => 'doc_list',
                    'page_title'      => 'Edit Documents',
                    'docs'            => $docs,
            ];

            return view('dashboard.docs', $data);
        }
    }

    /**
     * 	Post route for creating / updating documents.
     */
    public function postDocs($id = '')
    {
        $user = Auth::user();

        if (!$user->can('admin_manage_documents')) {
            return Redirect::to('/participa/dashboard')->with('message', trans('messages.nopermission'));
        }

        //Creating new document
        if ($id == '') {
            $title = Input::get('title');
            $slug = str_replace([' ', '.'], ['-', ''], strtolower($title));
            $doc_details = Input::all();

            $rules = ['title' => 'required'];
            $validation = Validator::make($doc_details, $rules);
            if ($validation->fails()) {
                die($validation);

                return Redirect::route('dashboard/docs')->withInput()->withErrors($validation);
            }

            try {
                $doc = new Doc();
                $doc->title = $title;
                $doc->slug = $slug;
                $doc->save();
                $doc->sponsor()->sync([$user->id]);

                $starter = new DocContent();
                $starter->doc_id = $doc->id;
                $starter->content = 'New Doc Content';
                $starter->save();

                $doc->init_section = $starter->id;
                $doc->save();

                return Redirect::route('dashboardShowsDoc', [$doc->id])->with('success_message', trans('messages.createddoc'));
            } catch (Exception $e) {
                return Redirect::route('dashboard/docs')->withInput()->with('error', $e->getMessage());
            }
        } else {
            return Response::error('404');
        }
    }

    /**
     * 	PUT route for saving documents.
     */
    public function putDocs($id = '')
    {
        $user = Auth::user();

        if (!$user->can('admin_manage_documents')) {
            return Redirect::to('/participa/dashboard')->with('message', trans('messages.nopermission'));
        }

        $content = Input::get('content');
        $content_id = Input::get('content_id');

        if ($content_id) {
            try {
                $doc_content = DocContent::find($content_id);
            } catch (Exception $e) {
                return Redirect::to('/participa/dashboard/docs/'.$id)->with('error', ucfirst(strtolower('Error '.trans('messages.saving').' '.trans('messages.the').' '.trans('messages.document'))).': '.$e->getMessage());
            }
        } else {
            $doc_content = new DocContent();
        }

        $doc_content->doc_id = $id;
        $doc_content->content = $content;
        $doc_content->save();

        Event::fire(MadisonEvent::DOC_EDITED, $doc);

        $doc = Doc::find($id);
        $doc->indexContent($doc_content);

        return Redirect::to('dashboard/docs/'.$id)->with('success_message', trans('messages.saveddoc'));
    }
}
