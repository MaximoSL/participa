<?php

namespace MXAbierto\Participa\Http\Controllers\Api;

use Illuminate\Support\Facades\Input;
use MXAbierto\Participa\Models\Date;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\DocMeta;
use MXAbierto\Participa\Models\DocContent;
use MXAbierto\Participa\Models\Category;
use MXAbierto\Participa\Models\MadisonEvent;
use MXAbierto\Participa\Models\UserMeta;

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

        $doc = Doc::with('content', 'categories', 'introtext')->find($doc);

        return response()->json($doc);
    }

    public function getDocs()
    {
        $perPage = Input::get('per_page', 20);
        $orderBy = Input::get('order', 'updated_at');

        $availableFilters = ['categories', 'statuses', 'dates'];

        $docs = Doc::with($availableFilters);

        if (Input::has('q')) {
            $search = Input::get('q');

            $docs->where('title', 'LIKE', '%'.$search.'%');
        }

        if (Input::has('filter')) {
            $filter = explode(':', Input::get('filter'));

            if (in_array($filter[0], $availableFilters)) {
                $docs->whereHas($filter[0], function($query) use ($filter) {
                    $query->where('id', '=', $filter[1]);
                });
            }
        }

        $docs = $docs->orderBy($orderBy, 'DESC')->paginate($perPage);

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
        $doc->title = Input::get('title');
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
        if ($old_slug != Input::get('slug')) {
            $doc->slug = Input::get('slug');
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
        $doc_content->content = Input::get('content');
        $doc_content->save();
        $doc->content([$doc_content]);
        $doc->save();

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

        $docs = Doc::take(10)->with('categories')->orderBy('updated_at', 'DESC')->get();

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

        $categories = Input::get('categories');
        $categoryIds = [];

        foreach ($categories as $category) {
            $toAdd = Category::where('name', $category['text'])->first();

            if (!isset($toAdd)) {
                $toAdd = new Category();
            }

            $toAdd->name = $category['text'];
            $toAdd->save();

            array_push($categoryIds, $toAdd->id);
        }

        $doc->categories()->sync($categoryIds);
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

        $text = Input::get('intro-text');
        $introText->meta_value = $text;

        $introText->save();

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
        $sponsor = Input::get('sponsor');

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

        $response['messages'][0] = ['text' => ucfirst(strtolower(trans('messages.sponsor').' '.trans('messages.saved'))), 'severity' => 'info'];

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

        $status = Input::get('status');

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

        $date = Input::get('date');

        $returned = new Date();
        $returned->label = $date['label'];
        $returned->date = date('Y-m-d H:i:s', strtotime($date['date']));

        $doc->dates()->save($returned);

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
        $input = Input::get('date');
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
        $doc = Doc::with('statuses')->first();

        $statuses = $doc->statuses;

        return response()->json($statuses);
    }
}
