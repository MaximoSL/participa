<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doc extends Model
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

    public function content()
    {
        return $this->hasOne('MXAbierto\Participa\Models\DocContent');
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

    public function sponsorName()
    {
        $sponsor = $this->sponsor->first();

        if ($sponsor instanceof User) {
            $display_name = $sponsor->fname.' '.$sponsor->lname;
        } else {
            $display_name = '';
        }

        return $display_name;
    }

    public function getLink()
    {
        return URL::to('docs/'.$this->slug);
    }


    public static function createEmptyDocument(array $params)
    {
        $defaults = [
            'content'     => 'New Document Content',
            'sponsor'     => null,
            'sponsorType' => null,
        ];

        $params = array_replace_recursive($defaults, $params);

        if (is_null($params['sponsor'])) {
            throw new \Exception('Sponsor Param Required');
        }

        $document = new self();

        DB::transaction(function () use ($document, $params) {
            $document->title = $params['title'];
            $document->save();

            switch ($params['sponsorType']) {
                case static::SPONSOR_TYPE_INDIVIDUAL:
                    $document->userSponsor()->sync([$params['sponsor']]);
                    break;
                case static::SPONSOR_TYPE_GROUP:
                    $document->groupSponsor()->sync([$params['sponsor']]);
                    break;
                default:
                    throw new \Exception('Invalid Sponsor Type');
            }

            $template = new DocContent();
            $template->doc_id = $document->id;
            $template->content = 'New Document Content';
            $template->save();

            $document->init_section = $template->id;
            $document->save();
        });

        Event::fire(MadisonEvent::NEW_DOCUMENT, $document);

        return $document;
    }

    public function save(array $options = [])
    {
        if (empty($this->slug)) {
            $this->slug = $this->getSlug();
        }

        return parent::save($options);
    }

    public function getSlug()
    {
        if (empty($this->title)) {
            throw new Exception("Can't get a slug - empty title");
        }

        return str_replace(
                    [' ', '.', ',', '#'],
                    ['-', '', '', ''],
                    strtolower($this->title));
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
}
