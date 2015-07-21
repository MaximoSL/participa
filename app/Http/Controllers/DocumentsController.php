<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\DocContent;
use MXAbierto\Participa\Models\MadisonEvent;

class DocumentsController extends AbstractController
{
    /**
     * Creates a new documents controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Lists all documents ownend by the logged user.
     *
     * @return \Illuminate\View\View
     */
    public function getList()
    {
        $documents = Doc::allOwnedBy(Auth::user()->id);

        return view('documents.list', ['doc_count' => $documents->count(), 'documents' => $documents]);
    }

    /**
     * Create a new document.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postCreateDocument(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $input = $request->all();

        try {
            $docOptions = [
                'title' => $input['title'],
            ];

            $user = Auth::user();

            $activeGroup = Session::get('activeGroupId');

            if ($activeGroup > 0) {
                $group = Group::where('id', '=', $activeGroup)->first();

                if (!$group) {
                    return redirect()->route('documents')->withInput()->with('error', trans('messages.invalidgroup'));
                }

                if (!$group->userHasRole($user, Group::ROLE_EDITOR) && !$group->userHasRole($user, Group::ROLE_OWNER)) {
                    return redirect()->route('documents')->withInput()->with('error', ucfirst(strtolower(trans('messages.nopermission').' '.trans('messages.tocreate').' '.trans('messages.document').' '.trans('messages.forgroup'))));
                }

                $docOptions['sponsor'] = $activeGroup;
                $docOptions['sponsorType'] = Doc::SPONSOR_TYPE_GROUP;
            } else {
                if (!$user->hasRole(Role::ROLE_INDEPENDENT_SPONSOR)) {
                    return redirect()->route('documents')->withInput()->with('error', ucfirst(strtolower(trans('messages.nopermission').' '.trans('messages.tocreate').' '.trans('messages.document').' '.trans('messages.asindividual'))));
                }

                $docOptions['sponsor'] = Auth::user()->id;
                $docOptions['sponsorType'] = Doc::SPONSOR_TYPE_INDIVIDUAL;
            }

            $document = Doc::createEmptyDocument($docOptions);

            if ($activeGroup > 0) {
                event(MadisonEvent::NEW_GROUP_DOCUMENT, ['document' => $document, 'group' => $group]);
            }

            return redirect()->to("documents/edit/{$document->id}")->with('success_message', trans('messages.saveddoc'));
        } catch (\Exception $e) {
            return redirect()->to('documents')->withInput()->with('error', ucfirst(strtolower(trans('messages.sorry').', '.trans('messages.therewaserror')))." - {$e->getMessage()}");
        }
    }

    public function saveDocumentEdits($documentId)
    {
        $content = Input::get('content');
        $contentId = Input::get('content_id');

        if (empty($content)) {
            return redirect()->route('documents')->with('error', ucfirst(strtolower(trans('messages.needtoinclude').' '.trans('messages.content').' '.trans('messages.to').' '.trans('messages.save'))));
        }

        if (!empty($contentId)) {
            $docContent = DocContent::find($contentId);
        } else {
            $docContent = new DocContent();
        }

        if (!$docContent instanceof DocContent) {
            return redirect()->route('documents')->with('error', ucfirst(strtolower(trans('messages.unable').' '.trans('messages.tolocate').' '.trans('messages.the').' '.trans('messages.document').' '.trans('messages.to').' '.trans('messages.save'))));
        }

        $document = Doc::find($documentId);

        if (!$document instanceof Doc) {
            return redirect()->route('documents')->with('error', ucfirst(strtolower(trans('messages.unable').' '.trans('messages.tolocate').' '.trans('messages.the').' '.trans('messages.document'))));
        }

        if (!$document->canUserEdit(Auth::user())) {
            return redirect()->route('documents')->with('error', ucfirst(strtolower(trans('messages.notauthorized').' '.trans('messages.tosave').' '.trans('messages.document'))));
        }

        $docContent->doc_id = $documentId;
        $docContent->content = $content;

        try {
            DB::transaction(function () use ($docContent, $content, $documentId, $document) {
                $docContent->save();
            });
        } catch (\Exception $e) {
            return redirect()->route('documents')->with('error', ucfirst(strtolower(trans('messages.therewaserror').' '.trans('messages.saving').' '.trans('messages.the').' '.trans('messages.document'))).": {$e->getMessage()}");
        }

        //Fire document edited event for admin notifications
        $doc = Doc::find($docContent->doc_id);
        event(MadisonEvent::DOC_EDITED, $doc);

        return redirect()->route('documents')->with('success_message', trans('messages.saveddoc'));
    }

    public function editDocument($documentId)
    {
        $doc = Doc::find($documentId);

        if (is_null($doc)) {
            return redirect()->route('documents')->with('error', trans('messages.documentnotfound'));
        }

        if (!$doc->canUserEdit(Auth::user())) {
            return redirect()->route('documents')->with('error', ucfirst(strtolower(trans('messages.notauthorized').' '.trans('messages.toviewdocument'))));
        }

        return view('documents.edit', [
            'page_id'     => 'edit_doc',
            'page_title'  => "Editing {$doc->title}",
            'doc'         => $doc,
            'contentItem' => $doc->content()->where('parent_id')->first(),
        ]);
    }
}
