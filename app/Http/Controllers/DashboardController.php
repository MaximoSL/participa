<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

/**
 * 	Controller for admin dashboard.
 */
class DashboardController extends AbstractController
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
     * 	Dashboard Index View.
     */
    public function getIndex()
    {
        return view('dashboard.index', [
            'page_id'        => 'dashboard',
            'page_title'     => 'Dashboard',
        ]);
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

    /**
     * 	Document Creation/List or Document Edit Views.
     */
    public function getDocs($id = '')
    {
        $user = Auth::user();

        if (!$user->can('admin_manage_documents')) {
            return Redirect::to('/participa/dashboard')->with('message', trans('messages.nopermission'));
        }

        if ($id == '') {
            $docs = Doc::all();

            $data = [
                    'page_id'         => 'doc_list',
                    'page_title'      => 'Edit Documents',
                    'docs'            => $docs,
            ];

            return view('dashboard.docs', $data);
        } else {
            $doc = Doc::find($id);
            if (isset($doc)) {
                $data = [
                        'page_id'        => 'edit_doc',
                        'page_title'     => 'Edit '.$doc->title,
                        'doc'            => $doc,
                        // Just get the first content element.  We only have one, now.
                        'contentItem' => $doc->content()->where('parent_id')->first(),
                ];

                return view('documents.edit', $data);
            } else {
                return Response::error('404');
            }
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
