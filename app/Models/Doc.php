<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use McCool\LaravelAutoPresenter\HasPresenter;

class Doc extends Model implements HasPresenter
{
    use SoftDeletes;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamp = true;

    const TYPE = 'doc';
    const SPONSOR_TYPE_INDIVIDUAL = 'individual';
    const SPONSOR_TYPE_GROUP = 'group';

    public function getEmbedCode()
    {
        $dom = new \DOMDocument();

        $docSrc = route('docs.embed', $this->slug);

        $insertElement = $dom->createElement('div');

        $containerElement = $dom->createElement('iframe');
        $containerElement->setAttribute('id', '__ogFrame');
        $containerElement->setAttribute('width', 300);
        $containerElement->setAttribute('height', 500);
        $containerElement->setAttribute('src', $docSrc);
        $containerElement->setAttribute('frameBorder', 0);

        $insertElement->appendChild($containerElement);

        return $dom->saveHtml($insertElement);
    }

    public function introtext()
    {
        return $this->hasMany('MXAbierto\Participa\Models\DocMeta')->where('meta_key', '=', 'intro-text');
    }

    public function dates()
    {
        return $this->hasMany('MXAbierto\Participa\Models\Date');
    }

    public function authors()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\User');
    }

    public function group()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\Group');
    }

    public function userSponsor()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\User');
    }

    public function statuses()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\Status');
    }

    public function categories()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\Category');
    }

    public function comments()
    {
        return $this->hasMany('MXAbierto\Participa\Models\Comment');
    }

    public function annotations()
    {
        return $this->hasMany('MXAbierto\Participa\Models\Annotation');
    }

    public function contents()
    {
        return $this->hasMany('MXAbierto\Participa\Models\DocContent');
    }

    public function content()
    {
        return $this->hasOne('MXAbierto\Participa\Models\DocContent')->whereNull('parent_id');
    }

    public function doc_meta()
    {
        return $this->hasMany('MXAbierto\Participa\Models\DocMeta');
    }

    public function canUserEdit($user)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if (in_array($user->id, $this->authors->lists('id')->all())) {
            return true;
        }

        return false;
    }

    public function getLink()
    {
        return route('docs.doc', $this->slug);
    }

    public static function createEmptyDocument(array $params)
    {
        $defaults = [
            'content' => 'New Document Content',
        ];

        $params = array_replace_recursive($defaults, $params);

        $document = new self();

        DB::transaction(function () use ($document, $params) {
            $document->title = $params['title'];

            // Generate unique slug
            $doc_model = new self();
            $slug = Str::slug($params['title'], '-');
            $slugCount = count($doc_model->whereRaw("slug REGEXP '^{$slug}(-[0-9]+)?$' and id != '{$doc_model->id}'")->get());
            $document->slug = ($slugCount > 0) ? "{$slug}-{$slugCount}" : $slug;

            $document->save();

            $template = new DocContent();
            $template->doc_id = $document->id;
            $template->content = 'New Document Content';
            $template->save();

            $document->init_section = $template->id;
            $document->save();
        });

        event(MadisonEvent::NEW_DOCUMENT, $document);

        return $document;
    }

    public static function allOwnedBy($userId)
    {
        $rawDocs = \DB::select(
            \DB::raw(
                'SELECT docs.* FROM
					(SELECT doc_id
					   FROM doc_group, group_members
					  WHERE doc_group.group_id = group_members.group_id
					    AND group_members.user_id = ?
					UNION ALL
					 SELECT doc_id
					   FROM doc_user
					  WHERE doc_user.user_id = ?
				    ) DocUnion, docs
				  WHERE docs.id = DocUnion.doc_id
			   GROUP BY docs.id'
            ),
            [$userId, $userId]
        );

        $results = new Collection();

        foreach ($rawDocs as $row) {
            $obj = new static();

            foreach ($row as $key => $val) {
                $obj->$key = $val;
            }

            $results->add($obj);
        }

        return $results;
    }

    public static function getAllValidSponsors()
    {
        $userMeta = UserMeta::where('meta_key', '=', UserMeta::TYPE_INDEPENDENT_SPONSOR)
                            ->where('meta_value', '=', 1)
                            ->get();

        $groups = Group::where('status', '=', Group::STATUS_ACTIVE)
                        ->get();

        $results = new Collection();

        $userIds = [];

        foreach ($userMeta as $m) {
            $userIds[] = $m->user_id;
        }

        if (!empty($userIds)) {
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $row = [
                        'display_name' => "{$user->fname} {$user->lname}",
                        'sponsor_type' => 'individual',
                        'id'           => $user->id,
                ];

                $results->add($row);
            }
        }

        foreach ($groups as $group) {
            $row = [
                    'display_name' => $group->display_name,
                    'sponsor_type' => 'group',
                    'id'           => $group->id,
            ];

            $results->add($row);
        }

        return $results;
    }

    public static function findDocBySlug($slug = null)
    {
        //Retrieve requested document
        $doc = static::where('slug', $slug)
                     ->with('statuses')
                     ->with('userSponsor')
                     ->with('groupSponsor')
                     ->with('categories')
                     ->with('dates')
                     ->first();

        if (!isset($doc)) {
            return;
        }

        return $doc;
    }

    /**
     * Get the presenter class.
     *
     * @return string
     */
    public function getPresenterClass()
    {
        return 'MXAbierto\Participa\Presenters\DocumentPresenter';
    }
}
