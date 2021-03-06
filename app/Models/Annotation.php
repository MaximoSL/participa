<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Annotation extends Model implements ActivityInterface
{
    use SoftDeletes;

    const INDEX_TYPE = 'annotation';

    const ANNOTATION_CONSUMER = 'Madison';

    const ACTION_LIKE = 'like';
    const ACTION_DISLIKE = 'dislike';
    const ACTION_FLAG = 'flag';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'annotations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['quote', 'text', 'uri', 'seen'];

    public function doc()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\Doc', 'doc_id');
    }

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }

    public function comments()
    {
        return $this->hasMany('MXAbierto\Participa\Models\AnnotationComment', 'annotation_id');
    }

    public function tags()
    {
        return $this->hasMany('MXAbierto\Participa\Models\AnnotationTag', 'annotation_id');
    }

    public function permissions()
    {
        return $this->hasMany('MXAbierto\Participa\Models\AnnotationPermission', 'annotation_id');
    }

    public static function createFromAnnotatorArray(array $input)
    {
        if (isset($input['id'])) {
            $retval = static::firstOrNew(['id' => $input['id']]);
        } else {
            $retval = new static();
        }

        $retval->doc_id = (int) $input['doc_id'];

        if (isset($input['user']) && is_array($input['user'])) {
            $retval->user_id = (int) $input['user']['id'];
        }

        if (isset($input['quote'])) {
            $retval->quote = $input['quote'];
        }

        if (isset($input['text'])) {
            $retval->text = $input['text'];
        }

        if (isset($input['uri'])) {
            $retval->uri = $input['uri'];
        }

        DB::transaction(function () use ($retval, $input) {

            $retval->save();

            if (isset($input['ranges'])) {
                foreach ($input['ranges'] as $range) {
                    $rangeObj = AnnotationRange::firstByRangeOrNew([
                            'annotation_id' => $retval->id,
                            'start_offset'  => $range['startOffset'],
                            'end_offset'    => $range['endOffset'],
                    ]);

                    $rangeObj->start = $range['start'];
                    $rangeObj->end = $range['end'];

                    $rangeObj->save();
                }
            }

            if (isset($input['comments']) && is_array($input['comments'])) {
                foreach ($input['comments'] as $comment) {
                    $commentObj = AnnotationComment::firstOrNew([
                            'id'            => (int) $comment['id'],
                            'annotation_id' => $retval->id,
                            'user_id'       => (int) $comment['user']['id'],
                    ]);

                    $commentObj->text = $comment['text'];

                    $commentObj->save();
                }
            }

            $permissions = [];

            if (isset($input['permissions']) && is_array($input['permissions'])) {
                foreach ($input['permissions']['read'] as $userId) {
                    $userId = (int) $userId;

                    if (!isset($permissions[$userId])) {
                        $permissions[$userId] = ['read' => false, 'update' => false, 'delete' => false, 'admin' => false];
                    }

                    $permissions[$userId]['read'] = true;
                }

                foreach ($input['permissions']['update'] as $userId) {
                    $userId = (int) $userId;

                    if (!isset($permissions[$userId])) {
                        $permissions[$userId] = ['read' => false, 'update' => false, 'delete' => false, 'admin' => false];
                    }

                    $permissions[$userId]['update'] = true;
                }

                foreach ($input['permissions']['delete'] as $userId) {
                    $userId = (int) $userId;

                    if (!isset($permissions[$userId])) {
                        $permissions[$userId] = ['read' => false, 'update' => false, 'delete' => false, 'admin' => false];
                    }

                    $permissions[$userId]['delete'] = true;
                }

                foreach ($input['permissions']['admin'] as $userId) {
                    $userId = (int) $userId;

                    if (!isset($permissions[$userId])) {
                        $permissions[$userId] = ['read' => false, 'update' => false, 'delete' => false, 'admin' => false];
                    }

                    $permissions[$userId]['admin'] = true;
                }
            }

            foreach ($permissions as $userId => $perms) {
                $userId = (int) $userId;

                $permissionsObj = AnnotationPermission::firstOrNew([
                    'annotation_id' => $retval->id,
                    'user_id'       => $userId,
                ]);

                $permissionsObj->read = (int) $perms['read'];
                $permissionsObj->update = (int) $perms['update'];
                $permissionsObj->delete = (int) $perms['delete'];
                $permissionsObj->admin = (int) $perms['admin'];

                $permissionsObj->save();
            }

            if (isset($input['tags']) && is_array($input['tags'])) {
                foreach ($input['tags'] as $tag) {
                    AnnotationTag::where('annotation_id', '=', $retval->id)->delete();

                    $tag = AnnotationTag::firstOrNew([
                        'annotation_id' => $retval->id,
                        'tag'           => strtolower($tag),
                    ]);

                    $tag->save();
                }
            }

        });

        return $retval;
    }

    public function toAnnotatorArray($userId = null)
    {
        $item = $this->toArray();
        $item['created'] = $item['created_at'];
        $item['updated'] = $item['updated_at'];
        $item['annotator_schema_version'] = 'v1.0';
        $item['ranges'] = [];
        $item['tags'] = [];
        $item['comments'] = [];
        $item['permissions'] = [
            'read'   => [],
            'update' => [],
            'delete' => [],
            'admin'  => [],
        ];

        $comments = AnnotationComment::where('annotation_id', '=', $item['id'])->get();

        foreach ($comments as $comment) {
            $user = User::find($comment['user_id']);

            $item['comments'][] = [
                'id'      => $comment->id,
                'text'    => $comment->text,
                'created' => $comment->created_at->toRFC2822String(),
                'updated' => $comment->updated_at->toRFC2822String(),
                'user'    => [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => "{$user->fname} {$user->lname[0]}",
                ],
            ];
        }

        $ranges = AnnotationRange::where('annotation_id', '=', $item['id'])->get();

        foreach ($ranges as $range) {
            $item['ranges'][] = [
                'start'       => $range['start'],
                'end'         => $range['end'],
                'startOffset' => $range['start_offset'],
                'endOffset'   => $range['end_offset'],
            ];
        }

        $user = User::where('id', '=', $item['user_id'])->first();
        $item['user'] = array_intersect_key($user->toArray(), array_flip(['id', 'email']));
        $item['user']['name'] = $user->fname.' '.$user->lname{0};

        $item['consumer'] = static::ANNOTATION_CONSUMER;

        $tags = AnnotationTag::where('annotation_id', '=', $item['id'])->get();

        foreach ($tags as $tag) {
            $item['tags'][] = $tag->tag;
        }

        $permissions = AnnotationPermission::where('annotation_id', '=', $item['id'])->get();

        foreach ($permissions as $perm) {
            if ($perm->read) {
                $item['permissions']['read'][] = $perm['user_id'];
            }

            if ($perm->update) {
                $item['permissions']['update'][] = $perm['user_id'];
            } else {
                $item['permissions']['update'][] = '0';
            }

            if ($perm->delete) {
                $item['permissions']['delete'][] = $perm['user_id'];
            } else {
                $item['permissions']['delete'][] = '0';
            }

            if ($perm->admin) {
                $item['permissions']['admin'][] = $perm['user_id'];
            } else {
                $item['permissions']['admin'][] = '0';
            }
        }

        if (!is_null($userId)) {
            $noteModel = NoteMeta::where('user_id', '=', $userId)
                                 ->where('meta_key', '=', NoteMeta::TYPE_USER_ACTION)
                                 ->take(1)->first();

            if (!is_null($noteModel)) {
                $item['user_action'] = $noteModel->meta_value;
            }
        }

        $item['likes'] = $this->likes();
        $item['dislikes'] = $this->dislikes();
        $item['flags'] = $this->flags();
        $item['seen'] = $this->seen;

        $item = array_intersect_key($item, array_flip([
            'id', 'annotator_schema_version', 'created', 'updated',
            'text', 'quote', 'uri', 'ranges', 'user', 'consumer', 'tags',
            'permissions', 'likes', 'dislikes', 'flags', 'seen', 'comments', 'doc_id',
            'user_action',
        ]));

        return $item;
    }

    public static function loadAnnotationsForAnnotator($docId, $annotationId = null, $userId = null)
    {
        $annotations = static::where('doc_id', '=', $docId);

        if (!is_null($annotationId)) {
            $annotations->where('id', '=', $annotationId);
        }

        $annotations = $annotations->get();

        $retval = [];
        foreach ($annotations as $annotation) {
            $retval[] = $annotation->toAnnotatorArray();
        }

        return $retval;
    }

    public function delete()
    {
        DB::transaction(function () {
            $deletedMetas = NoteMeta::where('annotation_id', '=', $this->id)->delete();
            $deletedComments = AnnotationComment::where('annotation_id', '=', $this->id)->delete();
            $deletedPermissions = AnnotationPermission::where('annotation_id', '=', $this->id)->delete();
            $deletedRanges = AnnotationRange::where('annotation_id', '=', $this->id)->delete();
            $deletedTags = AnnotationTag::where('annotation_id', '=', $this->id)->delete();

            return parent::delete();
        });
    }

    public function addOrUpdateComment(array $comment)
    {
        $obj = new AnnotationComment();
        $obj->text = $comment['text'];
        $obj->user_id = $comment['user']['id'];

        if (isset($comment['id'])) {
            $obj->id = $comment['id'];
        }

        $obj->annotation_id = $this->id;

        $obj->save();
        $obj->load('user');

        return $obj;
    }

    public static function getMetaCount($id, $action)
    {
        $annotation = static::where('annotation_id', '=', $id);

        $actionCount = $annotation->$action();

        return $actionCount;
    }

    public function saveUserAction($userId, $action)
    {
        switch ($action) {
            case static::ACTION_DISLIKE:
            case static::ACTION_LIKE:
            case static::ACTION_FLAG:
                break;
            default:
                throw new \InvalidArgumentException('Invalid Action to Add');
        }

        $actionModel = NoteMeta::where('annotation_id', '=', $this->id)
                                ->where('user_id', '=', $userId)
                                ->where('meta_key', '=', NoteMeta::TYPE_USER_ACTION)
                                ->take(1)->first();

        if (is_null($actionModel)) {
            $actionModel = new NoteMeta();
            $actionModel->meta_key = NoteMeta::TYPE_USER_ACTION;
            $actionModel->user_id = $userId;
            $actionModel->annotation_id = $this->id;
        }

        $actionModel->meta_value = $action;

        return $actionModel->save();
    }

    public function likes()
    {
        $likes = NoteMeta::where('annotation_id', $this->id)
                         ->where('meta_key', '=', NoteMeta::TYPE_USER_ACTION)
                         ->where('meta_value', '=', static::ACTION_LIKE)
                         ->count();

        return $likes;
    }

    public function dislikes()
    {
        $dislikes = NoteMeta::where('annotation_id', $this->id)
                             ->where('meta_key', '=', NoteMeta::TYPE_USER_ACTION)
                             ->where('meta_value', '=', static::ACTION_DISLIKE)
                             ->count();

        return $dislikes;
    }

    public function flags()
    {
        $flags = NoteMeta::where('annotation_id', $this->id)
                         ->where('meta_key', '=', NoteMeta::TYPE_USER_ACTION)
                         ->where('meta_value', '=', static::ACTION_FLAG)
                         ->count();

        return $flags;
    }

    /**
     *   Construct link for Annotation.
     *
     *   @param null
     *
     *   @return url
     */
    public function getLink()
    {
        $slug = DB::table('docs')->where('id', $this->doc_id)->pluck('slug');

        return route('docs.doc', ['slug' => $slug]).'#annotation_'.$this->id;
    }

    /**
     *   Create RSS item for Annotation.
     *
     *   @param null
     *
     *   @return array $item
     */
    public function getFeedItem()
    {
        $user = $this->user()->get()->first();

        $item['title'] = $user->fname.' '.$user->lname."'s Annotation";
        $item['author'] = $user->fname.' '.$user->lname;
        $item['link'] = $this->getLink();
        $item['pubdate'] = $this->updated_at;
        $item['description'] = $this->text;

        return $item;
    }
}
