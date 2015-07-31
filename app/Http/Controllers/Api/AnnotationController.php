<?php

namespace MXAbierto\Participa\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use MXAbierto\Participa\Models\Annotation;
use MXAbierto\Participa\Models\AnnotationComment;
use MXAbierto\Participa\Models\AnnotationPermission;
use MXAbierto\Participa\Models\AnnotationRange;
use MXAbierto\Participa\Models\AnnotationTag;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\MadisonEvent;

/**
 * 	Controller for Document actions.
 */
class AnnotationController extends AbstractApiController
{
    /**
     * Creates a new annotation controller.
     *
     * @return void
     */
    public function __construct()
    {
        $this->beforeFilter('auth', ['on' => ['post', 'put', 'delete']]);
    }

    //Route for /api/docs{doc}/annotation/{annotation}
    //	Returns json annotation if id found,
    //		404 with error message if id not found,
    //		404 if no id passed

    /**
     * Get annotations by document ID and annotation ID.
     *
     * @param int    $docId
     * @param string $annotationId optional, if not provided get all
     *
     * @throws Exception
     */
    public function getIndex($docId, $annotationId = null)
    {
        try {
            $userId = null;

            if (Auth::check()) {
                $userId = Auth::user()->id;
            }

            $results = Annotation::loadAnnotationsForAnnotator($docId, $annotationId, $userId);
        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }

        if (isset($annotationId)) {
            return response()->json($results[0]);
        }

        return response()->json($results);
    }

    /**
     * Create a new annotation.
     *
     * @param document ID $doc
     *
     * @throws Exception
     *
     * @return 303 redirect to annotation link
     */
    public function postIndex($doc)
    {
        $body = Input::all();
        $body['doc_id'] = $doc;
        $is_edit = false;

        //Check for edit tag
        if (in_array('edit', $body['tags'])) {
            $is_edit = true;

            //If no explanation present, throw error
            if (!isset($body['explanation'])) {
                throw new Exception(trans('messages.explanationrequired'));
            }
        }

        $id = DB::transaction(function () use ($body, $doc, $is_edit) {
            $annotation = new Annotation();
            $annotation->doc_id = $doc;
            $annotation->user_id = Auth::user()->id;
            $annotation->quote = $body['quote'];
            $annotation->text = $body['text'];
            $annotation->uri = $body['uri'];

            $annotation->save();

            foreach ($body['ranges'] as $range) {
                $rangeObj = new AnnotationRange();
                $rangeObj->annotation_id = $annotation->id;
                $rangeObj->start_offset = $range['startOffset'];
                $rangeObj->end_offset = $range['endOffset'];
                $rangeObj->start = $range['start'];
                $rangeObj->end = $range['end'];

                $rangeObj->save();
            }

            $permissions = new AnnotationPermission();
            $permissions->annotation_id = $annotation->id;
            $permissions->user_id = Auth::user()->id;
            $permissions->read = 1;
            $permissions->update = 0;
            $permissions->delete = 0;
            $permissions->admin = 0;
            $permissions->save();

            foreach ($body['tags'] as $tag) {
                $tagObj = new AnnotationTag();
                $tagObj->annotation_id = $annotation->id;
                $tagObj->tag = $tag;
                $tagObj->save();
            }

            if ($is_edit) {
                $comment = new AnnotationComment();
                $comment->text = $body['explanation'];
                $comment->user_id = $annotation->user_id;
                $comment->annotation_id = $annotation->id;

                $comment->save();
            }

            return $annotation->id;
        });

        $annotation = Annotation::find($id);

        event(MadisonEvent::DOC_ANNOTATED, $annotation);

        return redirect()->route('getAnnotation', [$doc, $id, 303]);
    }

    public function postSeen($docId, $annotationId)
    {
        $allowed = false;

        $user = Auth::user();
        $user->load('docs');

        // Check user documents against current document
        foreach ($user->docs as $doc) {
            if ($doc->id == $docId) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            throw new Exception(ucfirst(strtolower(trans('messages.notauthorized').' '.trans('messages.tomark').' '.trans('messages.annotation').' '.trans('messages.asseen').'.')));
        }

        //The user is allowed to make this action
        $annotation = Annotation::find($annotationId);
        $annotation->seen = 1;
        $annotation->save();

        $doc = Doc::find($docId);
        $vars = ['sponsor' => $user->fname.' '.$user->lname, 'label' => 'annotation', 'slug' => $doc->slug, 'title' => $doc->title, 'text' => $annotation->text];
        $email = $annotation->user->email;

        Mail::queue('email.read', $vars, function ($message) use ($email) {
            $message->subject(trans('messages.feedbackviewedbysponsor'));
            $message->from('sayhello@opengovfoundation.org', 'Madison');
            $message->to($email); // Recipient address
        });

        return response()->json($annotation);
    }

    /**
     * Update an existing annotation.
     *
     * @param string $id
     */
    public function putIndex($id = null)
    {

        //If no id requested, return 404
        if ($id === null) {
            App::abort(404, trans('messages.notreceivedannotationid'));
        }

        $body = Input::all();

        $annotation = Annotation::createFromAnnotatorArray($body);

        return response()->json($annotation);
    }

    /**
     * Delete an annotation by doc ID and annotation ID.
     *
     * @param int $doc
     * @param int $annotation
     */
    public function deleteIndex($doc, $annotation)
    {
        //If no id requested, return 404
        if ($annotation === null) {
            App::abort(404, trans('messages.notreceivedannotationid'));
        }

        $annotation = Annotation::find($annotation);

        $ret = $annotation->delete();

        return response()->make(null, 204);
    }

    /**
     * Return search results for annotations.
     */
    public function getSearch()
    {
        return false;
    }

    public function getLikes($doc, $annotation = null)
    {
        if ($annotation === null) {
            App::abort(404, trans('messages.notreceivedannotationid'));
        }

        $likes = Annotation::getMetaCount($annotation, 'likes');

        return response()->json(['likes' => $likes]);
    }

    public function getDislikes($doc, $annotation = null)
    {
        if ($annotation === null) {
            App::abort(404, trans('messages.notreceivedannotationid'));
        }

        $dislikes = Annotation::getMetaCount($annotation, 'dislikes');

        return response()->json(['dislikes' => $dislikes]);
    }

    public function getFlags($doc, $annotation = null)
    {
        if ($annotation === null) {
            App::abort(404, trans('messages.notreceivedannotationid'));
        }

        $flags = Annotation::getMetaCount($annotation, 'flags');

        return response()->json(['flags' => $flags]);
    }

    public function postLikes($doc, $annotation = null)
    {
        if ($annotation === null) {
            App::abort(404, trans('messages.notreceivednoteid'));
        }

        $annotation = Annotation::find($annotation);
        $annotation->saveUserAction(Auth::user()->id, Annotation::ACTION_LIKE);

        //Load fields for notification
        $annotation->link = $annotation->getLink();
        $annotation->load('user');
        $annotation->type = 'annotation';

        event(MadisonEvent::NEW_ACTIVITY_VOTE, ['vote_type' => 'like', 'activity' => $annotation, 'user' => Auth::user()]);

        return response()->json($annotation->toAnnotatorArray());
    }

    public function postDislikes($doc, $annotation = null)
    {
        if ($annotation === null) {
            abort(404, trans('messages.notreceivednoteid'));
        }

        $annotation = Annotation::find($annotation);
        $annotation->saveUserAction(Auth::user()->id, Annotation::ACTION_DISLIKE);

        //Load fields for notification
        $annotation->link = $annotation->getLink();
        $annotation->load('user');
        $annotation->type = 'annotation';

        event(MadisonEvent::NEW_ACTIVITY_VOTE, ['vote_type' => 'dislike', 'activity' => $annotation, 'user' => Auth::user()]);

        return response()->json($annotation->toAnnotatorArray());
    }

    public function postFlags($doc, $annotation = null)
    {
        if ($annotation === null) {
            abort(404, trans('messages.notreceivednoteid'));
        }

        $annotation = Annotation::find($annotation);
        $annotation->saveUserAction(Auth::user()->id, Annotation::ACTION_FLAG);

        return response()->json($annotation->toAnnotatorArray());
    }

    public function postComments($docId, $annotationId)
    {
        $comment = Input::get('comment');

        $annotation = Annotation::where('doc_id', '=', $docId)
                                ->where('id', '=', $annotationId)
                                ->first();

        $annotation->link = $annotation->getLink();
        $annotation->type = 'annotation';

        $result = $annotation->addOrUpdateComment($comment);

        // TODO: Hack to allow notification events.  Needs cleaned up.
        $result->doc_id = $docId;
        $result->link = $result->getLink($docId);

        event(MadisonEvent::DOC_SUBCOMMENT, ['subcomment' => $result, 'parent' => $annotation]);

        return response()->json($result);
    }
}
