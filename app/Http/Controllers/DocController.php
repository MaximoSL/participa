<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\UserMeta;
use Roumen\Feed\Facades\Feed;

/**
 * 	Controller for Document actions.
 */
class DocController extends AbstractController
{
    /**
     * Creates a new doc controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->beforeFilter('auth', ['on' => ['post', 'put', 'delete']]);
    }

    /**
     * Get docs index.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = Input::get('per_page', 20);

        $docs = Doc::paginate($perPage);

        return view('doc.index', [
            'docs'       => $docs,
            'page_id'    => 'docs',
            'page_title' => 'All Documents',
        ]);
    }

    /**
     * Get a doc by slug.
     *
     * @param string $slug
     *
     * @return \Illuminate\Http\Response
     */
    public function getDoc($slug)
    {
        try {
            //Retrieve requested document
            $doc = Doc::with('statuses', 'userSponsor', 'categories', 'docCategories', 'docLayouts', 'docInstitutions', 'dates')->where('slug', $slug)->first();

            if (!$doc) {
                abort('404');
            }

            $showAnnotationThanks = false;

            if (Auth::check()) {
                $userId = Auth::user()->id;

                $userMeta = UserMeta::where('user_id', '=', $userId)
                                    ->where('meta_key', '=', UserMeta::TYPE_SEEN_ANNOTATION_THANKS)
                                    ->take(1)->first();

                if ($userMeta instanceof UserMeta) {
                    $showAnnotationThanks = !$userMeta->meta_value;
                } else {
                    $showAnnotationThanks = true;
                }
            }

            //Set data array
            $data = [
                'doc'                  => $doc,
                'page_id'              => strtolower(str_replace(' ', '-', $doc->title)),
                'page_title'           => $doc->title,
                'showAnnotationThanks' => $showAnnotationThanks,
            ];

            //Render the cofemer view and return
            if (in_array('cofemer', $doc->categories()->where('kind', 'layout')->lists('name', 'id')->all())) {
                return view('doc.reader.cofemer.index', $data);
            }

            //Render the votes view and return
            if (in_array('votos', $doc->categories()->where('kind', 'layout')->lists('name', 'id')->all())) {
                return view('doc.reader.votes.index', $data);
            }

            //Render view and return
            return view('doc.reader.index', $data);
        } catch (Exception $e) {
            return Redirect::route('home')->with('error', $e->getMessage());
        }
    }

    /**
     * Get embeded doc by slug.
     *
     * @param string $slug
     *
     * @return \Illuminate\Http\Response
     */
    public function getEmbedded($slug = null)
    {
        $doc = Doc::findDocBySlug($slug);

        if ($doc) {
            abort('404');
        }

        return view('doc.reader.embed', compact('doc'));
    }

    /**
     * Search for a document.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSearch()
    {
        $q = Input::get('q');

        $results = Doc::search(urldecode($q));

        $docs = [];

        foreach ($results['hits']['hits'] as $result) {
            $doc = Doc::find($result['_source']['id']);
            array_push($docs, $doc);
        }

        return view('doc.search.index', [
            'page_id'    => 'doc-search',
            'page_title' => 'Resultados de la búsqueda',
            'results'    => $docs,
            'query'      => $q,
        ]);
    }

    /**
     * Method to handle document RSS feeds.
     *
     * @param string $slug
     *
     * @return \Illuminate\Http\Response
     */
    public function getFeed($slug)
    {
        $doc = Doc::where('slug', $slug)->with('comments', 'annotations')->first();

        $feed = Feed::make();

        $feed->title = $doc->title;
        $feed->description = "Activity feed for {$doc->title}";
        $feed->link = route('docs.doc', $slug);
        $feed->pubdate = $doc->updated_at;
        $feed->lang = 'en';

        $activities = $doc->comments->merge($doc->annotations);

        $activities = $activities->sort(function ($a, $b) {
            return (strtotime($a['updated_at']) > strtotime($b['updated_at'])) ? -1 : 1;
        });

        foreach ($activities as $activity) {
            $item = $activity->getFeedItem();

            array_push($feed->items, $item);
        }

        return $feed->render('atom');
    }
}
