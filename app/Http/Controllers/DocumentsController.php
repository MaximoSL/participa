<?php

namespace MXAbierto\Participa\Http\Controllers;

use GrahamCampbell\Binput\Binput;
use GrahamCampbell\Binput\Facades\Binput as BinputFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\DocContent;
use MXAbierto\Participa\Models\MadisonEvent;
use MXAbierto\Participa\Models\Role;
use MXAbierto\Participa\Services\CSVParser;

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

        $input = BinputFacade::all();

        try {
            $docOptions = [
                'title' => $input['title'],
            ];

            $user = Auth::user();

            if (!$user->hasRole(Role::ROLE_INDEPENDENT_SPONSOR) && !$user->hasRole(Role::ROLE_ADMIN)) {
                return redirect()->route('documents')->withInput()->with('error', ucfirst(strtolower(trans('messages.nopermission').' '.trans('messages.tocreate').' '.trans('messages.document').' '.trans('messages.asindividual'))));
            }

            $docOptions['sponsor'] = Auth::user()->id;
            $docOptions['sponsorType'] = Doc::SPONSOR_TYPE_INDIVIDUAL;

            $document = Doc::createEmptyDocument($docOptions);

            $user->docs()->attach($document->id);

            return redirect()->route('documents.edit', $document->id)->with('success_message', trans('messages.saveddoc'));
        } catch (\Exception $e) {
            return redirect()->route('documents')->withInput()->with('error', ucfirst(strtolower(trans('messages.sorry').', '.trans('messages.therewaserror')))." - {$e->getMessage()}");
        }
    }

    public function saveDocumentEdits($documentId, Binput $request)
    {
        $content = $request->get('content');
        $contentId = $request->get('content_id');

        if ($request->file('doc_content_file')) {
            $allowed_file_mime_types = [
                'text/plain',
                'text/html',
            ];

            $allowed_file_extensions = [
                'txt',
                'md',
                'csv',
            ];

            $file = $request->file('doc_content_file');

            if (!in_array($file->getMimeType(), $allowed_file_mime_types) ||
                !in_array($file->getClientOriginalExtension(), $allowed_file_extensions)) {
                $error = [
                    'doc_content_file'  => trans('validation.mimes', [
                            'attribute' => trans('messages.file'),
                            'values'    => implode(', ', $allowed_file_extensions),
                        ]),
                    ];

                return redirect()->route('documents.edit', $documentId)->withErrors($error);
            }

            switch ($file->getClientOriginalExtension()) {
                case 'txt':
                case 'md':
                    $content = file_get_contents($file);
                    break;

                case 'csv':
                    $content = CSVParser::processCSVFileContent($file, 'doc_'.$documentId.'_');
                    break;
            }
        }

        $content = BinputFacade::clean($content);

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

        if (!$doc->content) {
            $template = new DocContent();
            $template->doc_id = $doc->id;
            $template->content = 'New Document Content';
            $template->save();

            $doc->init_section = $template->id;
            $doc->save();

            $empty_content_log = new Logger('Documento sin contenido');
            $empty_content_log->pushHandler(new StreamHandler(storage_path().'/logs/empty_content_log.log', Logger::INFO));
            $empty_content_log->addInfo('El documento '.$doc->id.' - '.$doc->title.', no tenía registro relacionado en la tabla doc_contents, el registro '.$template->id.' se ha creado en la tabla doc_contents');
        }

        return view('documents.edit', [
            'page_id'     => 'edit_doc',
            'page_title'  => "Editing {$doc->title}",
            'doc'         => $doc,
            'contentItem' => $doc->content()->where('parent_id')->first(),
        ]);
    }
}
