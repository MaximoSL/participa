<?php

namespace MXAbierto\Participa\Http\Controllers\Api;

use GrahamCampbell\Binput\Facades\Binput;
use Illuminate\Support\Facades\Auth;
use MXAbierto\Participa\Models\Category;
use MXAbierto\Participa\Models\Date;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\DocContent;
use MXAbierto\Participa\Models\DocMeta;
use MXAbierto\Participa\Models\Group;
use MXAbierto\Participa\Models\MadisonEvent;
use MXAbierto\Participa\Models\Role;
use MXAbierto\Participa\Models\Status;

/**
 * 	Controller for Document actions.
 */
class DocumentController extends AbstractApiController
{
    /**
     * Creates a new document controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getDoc', 'getDocs']]);
    }

    public function getDoc($doc)
    {
        $doc_id = $doc;

        $doc = Doc::with('content', 'categories', 'docCategories', 'introtext')->find($doc);

        return response()->json($doc);
    }

    public function getDocs()
    {
        $perPage = Binput::get('per_page', 20);
        $orderBy = Binput::get('order', 'updated_at');

        $perPage = (intval($perPage) > 0) ? intval($perPage) : 20;

        $availableFilters = ['docCategories', 'docInstitutions', 'statuses', 'dates'];

        $docs = Doc::with($availableFilters);

        if (Binput::has('q')) {
            $search = Binput::get('q');

            $docs->where('title', 'LIKE', '%'.$search.'%');
        }

        if (Binput::has('filter')) {
            $filter = explode(':', Binput::get('filter'));

            if (in_array($filter[0], $availableFilters)) {
                $docs->whereHas($filter[0], function ($query) use ($filter) {
                    $query->where('id', '=', $filter[1]);
                });
            }
        }

        $docs->leftJoin('doc_status', 'docs.id', '=', 'doc_status.doc_id');
        $docs->orderByRaw('FIELD(doc_status.status_id, 1, 2, 3) ASC');

        $docs->orderBy($orderBy, 'DESC');
        $docs = $docs->paginate($perPage);

        $response = [];
        $response['results'] = [];

        $response['pagination']['per_page'] = $docs->perPage();
        $response['pagination']['page'] = $docs->currentPage();
        $response['pagination']['count'] = $docs->total();

        foreach ($docs as $doc) {
            $return_doc = $doc->toArray();
            $return_doc['updated_at'] = date('c', strtotime($return_doc['updated_at']));
            $return_doc['created_at'] = date('c', strtotime($return_doc['created_at']));

            $response['results'][] = $return_doc;
        }

        return response()->json($response);
    }

    public function postTitle($id)
    {
        $doc = Doc::find($id);
        $doc->title = Binput::get('title');
        $doc->save();

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.title').' '.trans('messages.ofmale').' '.trans('messages.document').' '.trans('messages.saved'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function postSlug($id)
    {
        $doc = Doc::find($id);
        // Compare current and new slug
        $old_slug = $doc->slug;
        // If the new slug is different, save it
        if ($old_slug != Binput::get('slug')) {
            $doc->slug = Binput::get('slug');
            $doc->save();
            $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.docslug').' '.trans('messages.saved'))), 'severity' => 'info'];
        } else {
            // If the slugs are identical, the only way this could have happened is if the sanitize
            // function took out an invalid character and tried to submit an identical slug
            $response['messages'][0] = ['text' => trans('messages.invalidslugcharacter'), 'severity' => 'error'];
        }

        return response()->json($response);
    }

    public function postContent($id)
    {
        $doc = Doc::find($id);
        $doc_content = DocContent::firstOrCreate(['doc_id' => $doc->id]);
        $doc_content->content = Binput::get('content');
        $doc_content->save();
        $doc->content([$doc_content]);
        $doc->save();
        $doc->touch();

        event(MadisonEvent::DOC_EDITED, $doc);

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.doccontent').' '.trans('messages.saved'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function getRecent($query = null)
    {
        $recent = 10;

        if (isset($query)) {
            $recent = $query;
        }

        $docs = Doc::take(10)->with('categories', 'docCategories')->orderBy('updated_at', 'DESC')->get();

        foreach ($docs as $doc) {
            $doc->setActionCount();
        }

        return response()->json($docs);
    }

    public function getCategories($doc = null)
    {
        if (!isset($doc)) {
            $categories = Category::all();
        } else {
            $doc = Doc::find($doc);
            $categories = $doc->categories()->get();
        }

        return response()->json($categories);
    }

    public function postCategories($doc)
    {
        $doc = Doc::find($doc);

        $categories = Binput::get('categories');
        $categoryIds = [];

        foreach ($categories as $category) {
            $category['text'] = str_replace(' - ', '|', $category['text']);
            $category['text'] = explode('|', $category['text']);

            if (empty($category['text'][1])) {
                $category['text'][1] = 'category';
                // $response['status'] = 'error';
                // $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.therewaserror').', '.trans('messages.categorynotfound'))), 'severity' => 'error'];

                // return response()->json($response);
            }

            $toAdd = Category::where('name', $category['text'][0])->where('kind', $category['text'][1])->first();

            if (!isset($toAdd)) {
                $toAdd = new Category();
                $toAdd->name = $category['text'][0];
                $toAdd->save();
            }

            array_push($categoryIds, $toAdd->id);
        }

        $doc->categories()->sync($categoryIds);

        $doc->touch();

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.categories').' '.trans('messages.savedfeminineplural'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function getIntroText($doc)
    {
        $introText = DocMeta::where('meta_key', '=', 'intro-text')->where('doc_id', '=', $doc)->first();

        return response()->json($introText);
    }

    public function postIntroText($doc)
    {
        $introText = DocMeta::where('meta_key', '=', 'intro-text')->where('doc_id', '=', $doc)->first();

        if (!$introText) {
            $introText = new DocMeta();
            $introText->doc_id = $doc;
            $introText->meta_key = 'intro-text';
        }

        $text = Binput::get('intro-text');
        $introText->meta_value = $text;

        $introText->save();

        $doc = Doc::find($doc);
        $doc->touch();

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.docintrotext').' '.trans('messages.saved'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function hasSponsor($doc, $sponsor)
    {
        $result = Doc::find($doc)->sponsor()->find($sponsor);

        return response()->json($result);
    }

    public function getSponsor($doc)
    {
        $doc = Doc::find($doc);
        $sponsor = $doc->sponsor()->first();

        if ($sponsor) {
            $sponsor->sponsorType = get_class($sponsor);

            return response()->json($sponsor);
        }

        return response()->json();
    }

    public function postSponsor($doc)
    {
        $sponsor = Binput::get('sponsor');

        $doc = Doc::find($doc);
        $response = null;

        if (!isset($sponsor)) {
            $doc->sponsor()->sync([]);
        } else {
            switch ($sponsor['type']) {
                case 'user':
                    $user = User::find($sponsor['id']);
                    $doc->userSponsor()->sync([$user->id]);
                    $doc->groupSponsor()->sync([]);
                    $response = $user;
                    break;
                case 'group':
                    $group = Group::find($sponsor['id']);
                    $doc->groupSponsor()->sync([$group->id]);
                    $doc->userSponsor()->sync([]);
                    $response = $group;
                    break;
                default:
                    throw new Exception('Unknown sponsor type '.$type);
            }
        }

        $doc->touch();

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.sponsor').' '.trans('messages.saved'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function getGroup($doc)
    {
        $doc = Doc::find($doc);

        $group = $doc->group()->first();

        return response()->json($group);
    }

    public function postGroup($doc)
    {
        $toAdd = null;

        $group = Binput::get('group');

        $user = Auth::user();

        $doc = Doc::find($doc);

        if (!isset($group)) {
            $doc->group()->sync([]);
        } else {
            $toAdd = Group::where('name', $group['text'])->first();

            if(!$toAdd) {
                $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.invalidgroup'))), 'severity' => 'error'];
                return response()->json($response);
            }

            if (!$user->hasRole(Role::ROLE_ADMIN)) {
                if (!isset($toAdd) || !$user->groups->contains($toAdd->id)) {
                    $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.invalidgroup'))), 'severity' => 'error'];

                    return response()->json($response);
                }
            }

            // if (!isset($toAdd)) {
            //     $toAdd = new Group();
            //     $toAdd->name = $group['text'];
            // }
            $toAdd->save();

            $doc->group()->sync([$toAdd->id]);
        }

        $doc->touch();

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.document').' '.trans('messages.saved'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function getStatus($doc)
    {
        $doc = Doc::find($doc);

        $status = $doc->statuses()->first();

        return response()->json($status);
    }

    public function postStatus($doc)
    {
        $toAdd = null;

        $status = Binput::get('status');

        $doc = Doc::find($doc);

        if (!isset($status)) {
            $doc->statuses()->sync([]);
        } else {
            $toAdd = Status::where('label', $status['text'])->first();

            if (!isset($toAdd)) {
                $toAdd = new Status();
                $toAdd->label = $status['text'];
            }
            $toAdd->save();

            $doc->statuses()->sync([$toAdd->id]);
        }

        $doc->touch();

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.document').' '.trans('messages.saved'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function getDates($doc)
    {
        $doc = Doc::find($doc);

        $dates = $doc->dates()->get();

        return response()->json($dates);
    }

    public function postDate($doc)
    {
        $doc = Doc::find($doc);

        $date = Binput::get('date');

        $datetime = \DateTime::createFromFormat('d/m/y H:i', $date['date']);

        $returned = new Date();
        $returned->label = $date['label'];
        $returned->date = $datetime->format('Y-m-d H:i:s');

        $doc->dates()->save($returned);

        $doc->touch();

        return response()->json($returned);
    }

    public function deleteDate($doc, $date)
    {
        $date = Date::find($date);

        if (!isset($date)) {
            throw new Exception(ucfirst(strtolower(trans('messages.unable').' '.trans('messages.todelete').' '.trans('messages.thefeminine').' '.trans('messages.date').'. '.trans('messages.the').' '.trans('messages.dateid').' $date '.trans('messages.notfound'))));
        }

        $date->delete();

        return response()->json();
    }

    public function putDate($date)
    {
        $input = Binput::get('date');
        $date = Date::find($date);

        if (!isset($date)) {
            throw new Exception(ucfirst(strtolower(trans('messages.unable').' '.trans('messages.toupdate').' '.trans('messages.thefeminine').' '.trans('messages.date').'. '.trans('messages.the').' '.trans('messages.dateid').' $date '.trans('messages.notfound'))));
        }

        $newDate = date('Y-m-d H:i:s', strtotime((string) $input['date']));

        $date->label = $input['label'];
        $date->date = $newDate;

        $date->save();

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.document').' '.trans('messages.saved'))), 'severity' => 'info'];

        return response()->json($response);
    }

    public function getAllSponsorsForUser()
    {
        $retval = [
            'success'  => false,
            'sponsors' => [],
            'message'  => '',
        ];

        if (!Auth::check()) {
            $retval['message'] = ucfirst(strtolower(trans('messages.needlogin').' '.trans('messages.toperformcall')));

            return response()->json($retval);
        }

        $sponsors = Auth::user()->getValidSponsors();

        foreach ($sponsors as $sponsor) {
            switch (true) {
                case ($sponsor instanceof User):
                    $userSponsor = $sponsor->toArray();
                    $userSponsor['sponsorType'] = 'user';

                    $retval['sponsors'][] = $userSponsor;

                    break;
                case ($sponsor instanceof Group):

                    $groupSponsor = $sponsor->toArray();
                    $groupSponsor['sponsorType'] = 'group';

                    $retval['sponsors'][] = $groupSponsor;
                    break;
                default:
                    break;
            }
        }

        $retval['success'] = true;

        return response()->json($retval);
    }

    public function getAllSponsors()
    {
        $doc = Doc::with('sponsor')->first();
        $sponsors = $doc->sponsor;

        return response()->json($sponsors);
    }

    public function getAllStatuses()
    {
        $statuses = Status::all();

        return response()->json($statuses);
    }

    public function getAllGroups()
    {
        $user = Auth::user();

        $groups = [];

        if ($user->hasRole(Role::ROLE_ADMIN)) {
            $groups = Group::all();
        } elseif ($user->hasRole(Role::ROLE_INDEPENDENT_SPONSOR)) {
            $groups = $user->groups;
        }

        return response()->json($groups);
    }
}
