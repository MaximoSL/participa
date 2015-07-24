<?php

namespace MXAbierto\Participa\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use MXAbierto\Participa\Models\Comment;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\MadisonEvent;

/**
 * 	Controller for Document actions.
 */
class CommentController extends AbstractApiController
{
    /**
     * Creates a new api comment controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->beforeFilter('auth', ['on' => ['post', 'put', 'delete']]);
    }

    public function getIndex($doc, $comment = null)
    {
        try {
            $results = Comment::loadComments($doc, $comment, Auth::user());
        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($results);
    }

    public function postIndex($doc)
    {
        $user = Auth::user();
        $comment = Input::get('comment');

        $comment['private'] = (!empty($comment['private']) && $comment['private'] !== 'false') ? 1 : 0;

        $newComment = new Comment();
        $newComment->user_id = $user->id;
        $newComment->doc_id = $comment['doc']['id'];
        $newComment->text = $comment['text'];
        $newComment->private = $comment['private'];
        $newComment->save();

        event(MadisonEvent::DOC_COMMENTED, $newComment);

        $return = Comment::loadComments($newComment->doc_id, $newComment->id, $user);

        return response()->json($return);
    }

    public function postSeen($docId, $commentId)
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

        $comment = Comment::find($commentId);
        $comment->seen = 1;
        $comment->save();

        $doc = Doc::find($docId);
        $vars = ['sponsor' => $user->fname.' '.$user->lname, 'label' => 'comment', 'slug' => $doc->slug, 'title' => $doc->title, 'text' => $comment->text];
        $email = $comment->user->email;

        Mail::queue('email.read', $vars, function ($message) use ($email) {
            $message->subject(trans('messages.feedbackviewedbysponsor'));
            $message->from('sayhello@opengovfoundation.org', 'Madison');
            $message->to($email); // Recipient address
        });

        return response()->json($comment);
    }

    public function postLikes($docId, $commentId)
    {
        $comment = Comment::find($commentId);
        $comment->saveUserAction(Auth::user()->id, Comment::ACTION_LIKE);

        //Load fields for notification
        $comment->load('user');
        $comment->type = 'comment';

        event(MadisonEvent::NEW_ACTIVITY_VOTE, ['vote_type' => 'like', 'activity' => $comment, 'user' => Auth::user()]);

        return response()->json($comment->loadArray());
    }

    public function postDislikes($docId, $commentId)
    {
        $comment = Comment::find($commentId);
        $comment->saveUserAction(Auth::user()->id, Comment::ACTION_DISLIKE);

        //Load fields for notification
        $comment->load('user');
        $comment->type = 'comment';

        event(MadisonEvent::NEW_ACTIVITY_VOTE, ['vote_type' => 'dislike', 'activity' => $comment, 'user' => Auth::user()]);

        return response()->json($comment->loadArray());
    }

    public function postFlags($docId, $commentId)
    {
        $comment = Comment::find($commentId);
        $comment->saveUserAction(Auth::user()->id, Comment::ACTION_FLAG);

        return response()->json($comment->loadArray());
    }

    public function postComments($docId, $commentId)
    {
        $comment = Input::get('comment');

        $parent = Comment::where('doc_id', '=', $docId)
                                ->where('id', '=', $commentId)
                                ->first();

        $parent->load('user');
        $parent->type = 'comment';

        //Returns the new saved Comment with the User relationship loaded
        $result = $parent->addOrUpdateComment($comment);

        event(MadisonEvent::DOC_SUBCOMMENT, ['comment' => $result, 'parent' => $parent]);

        return response()->json($result);
    }

    public function destroy($docId, $commentId)
    {
        $comment = Comment::withTrashed()->find($commentId);
        $user = Auth::user();

        if (!$comment->canUserEdit($user, $docId)) {
            try {
                return redirect()->back()->with('error', ucfirst(strtolower(trans('messages.notauthorized'))));
            } catch (Exception $e) {
                return redirect()->route('home')->with('error', ucfirst(strtolower(trans('messages.notauthorized'))));
            }
        }

        $doc = Doc::find($docId);

        if ($comment->deleted_at && $doc->canUserEdit($user)) {
            $comment->restore();
        } elseif (!$comment->deleted_at) {
            $comment->delete();
        }

        return response()->json($comment->loadArray());
    }
}
