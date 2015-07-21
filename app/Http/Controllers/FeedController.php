<?php

namespace MXAbierto\Participa\Http\Controllers;

use McCool\LaravelAutoPresenter\PresenterDecorator;
use MXAbierto\Participa\Models\Annotation;
use MXAbierto\Participa\Models\Doc;
use MXAbierto\Participa\Models\User;
use Roumen\Feed\Facades\Feed;

/**
 * The feed controller class.
 *
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class FeedController extends AbstractController
{
    /**
     * Generate RSS feed for docs.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFeed(PresenterDecorator $presenter)
    {
        //Grab all documents
        $docs = Doc::with('content', 'authors')->orderBy('updated_at', 'DESC')->take(20)->get();

        $feed = Feed::make();
        $feed->title = 'Madison Documents';
        $feed->description = 'Latest 20 documents in Madison';
        $feed->link = route('feed');
        $feed->pubdate = $docs->first()->updated_at;
        $feed->lang = 'en';

        foreach ($docs as $doc) {
            $authorName = 'gob.mx';
            $author = $doc->authors->first();

            if ($author) {
                $authorName = $author->name;
            }

            $document = $presenter->decorate($doc);

            $item = [];
            $item['title'] = $doc->title;
            $item['author'] = $authorName;
            $item['link'] = route('docs.doc', $doc->slug);
            $item['pubdate'] = $doc->updated_at;
            $item['description'] = $doc->title;
            $item['content'] = $doc->formattedContent;

            $feed->items[] = $item;
        }

        return $feed->render('atom');
    }

    /**
     * Generate Sitemap.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSitemap()
    {
        $sitemap = app('sitemap');

        $pages = [route('about'), route('faq'), route('auth.login'), route('auth.signup')];

        foreach ($pages as $page) {
            $sitemap->add($page);
        }

        $docs = Doc::all();

        foreach ($docs as $doc) {
            $sitemap->add(route('docs.doc', $doc->slug));
        }

        $annotations = Annotation::all();

        foreach ($annotations as $annotation) {
            $sitemap->add(route('annotation.show', $annotation->id));
        }

        $users = User::all();

        foreach ($users as $user) {
            $sitemap->add(route('user.show', $user->id));
        }

        // show your sitemap (options: 'xml' (default), 'html', 'txt', 'ror-rss', 'ror-rdf')
        return $sitemap->render('xml');
    }
}
