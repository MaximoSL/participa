<?php

namespace MXAbierto\Participa\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

/**
 * This is the main routes class.
 *
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class MainRoutes
{
    /**
     * Define the main routes.
     *
     * @param \Illuminate\Contracts\Routing\Registrar $router
     *
     * @return void
     */
    public function map(Registrar $router)
    {
        // Static Pages
        $router->get('/', [
            'as'   => 'home',
            'uses' => 'HomeController@getHome',
        ]);
        $router->get('about', [
            'as'   => 'about',
            'uses' => 'PageController@getAbout',
        ]);
        $router->get('faq', [
            'as'   => 'faq',
            'uses' => 'PageController@getFaq',
        ]);
        $router->get('privacy-policy', [
            'as'   => 'privacy-policy',
            'uses' => 'PageController@getPrivacy',
        ]);
        $router->get('terms-and-conditions', [
            'as'   => 'terms-and-conditions',
            'uses' => 'PageController@getTerms',
        ]);
        $router->get('copyright', [
            'as'   => 'copyright',
            'uses' => 'PageController@getCopyright',
        ]);

        //User Routes
        $router->get('auth/login', [
            'as'   => 'auth.login',
            'uses' => 'AuthController@getLogin',
        ]);
        $router->post('auth/login', [
            'as'   => 'auth.login',
            'uses' => 'AuthController@postLogin',
        ]);
        $router->get('logout', [
            'as'   => 'auth.logout',
            'uses' => 'AuthController@getLogout',
        ]);
        $router->get('auth/signup', [
            'as'   => 'auth.signup',
            'uses' => 'AuthController@getSignup',
        ]);
        $router->post('auth/signup', [
            'as'   => 'auth.signup',
            'uses' => 'AuthController@postSignup',
        ]);

        $router->get('user/{id}', [
            'as'   => 'user.show',
            'uses' => 'UserController@getIndex',
        ]);
        $router->get('user/account', [
            'as'   => 'user.account',
            'uses' => 'AccountController@getEdit',
        ]);
        $router->patch('user/account', [
            'as'   => 'user.account',
            'uses' => 'AccountController@patchAccount',
        ]);
        $router->get('user/notifications', [
            'as'   => 'user.notifications',
            'uses' => 'NotificationsController@editNotifications',
        ]);
        $router->patch('user/notifications', [
            'as'   => 'user.notifications',
            'uses' => 'NotificationsController@editNotifications',
        ]);

        //Password Routes
        $router->get('password/remind', [
            'as'   => 'password.remind',
            'uses' => 'RemindersController@getRemind',
        ]);
        $router->post('password/remind', 'RemindersController@postRemind');
        $router->get('password/reset/{token}',  'RemindersController@getReset');
        $router->post('password/reset',  'RemindersController@postReset');

        // Confirmation email resend
        $router->get('verification/remind',  [
            'as'   => 'verification.remind',
            'uses' => 'RemindersController@getConfirmation',
        ]);
        $router->post('verification/remind', [
            'as'   => 'verification.remind',
            'uses' => 'RemindersController@postConfirmation',
        ]);

        // Document Routes
        $router->get('docs', [
            'as'   => 'docs',
            'uses' => 'HomeController@getHome',
        ]);
        $router->get('docs/{slug}', [
            'as'   => 'docs.doc',
            'uses' => 'DocController@getDoc',
        ]);
        $router->get('docs/embed/{slug}', [
            'as'   => 'docs.embed',
            'uses' => 'DocController@getEmbedded',
        ]);
        $router->get('docs/{slug}/feed', [
            'as'   => 'docs.feed',
            'uses' => 'DocController@getFeed',
        ]);
        $router->get('documents/search', 'DocumentsController@getSearch');
        $router->get('documents', [
            'as'   => 'documents',
            'uses' => 'DocumentsController@getList',
        ]);
        $router->get('documents/view/{documentId}', [
            'as'   => 'documents.view',
            'uses' => 'DocumentsController@viewDocument',
        ]);
        $router->post('documents/create', [
            'as'   => 'documents.create',
            'uses' => 'DocumentsController@postCreateDocument',
        ]);
        $router->get('documents/edit/{documentId}', [
            'as'   => 'documents.edit',
            'uses' => 'DocumentsController@editDocument',
        ]);
        $router->put('documents/edit/{documentId}', [
            'as'   => 'saveDocumentEdits',
            'uses' => 'DocumentsController@saveDocumentEdits',
        ]);
        $router->post('documents/save', 'DocumentsController@saveDocument');
        $router->delete('documents/delete/{slug}', 'DocumentsController@deleteDocument');
        $router->get('documents/sponsor/request', [
            'as'   => 'sponsorRequest',
            'uses' => 'SponsorController@getRequest',
        ]);
        $router->post('documents/sponsor/request', [
            'as'   => 'sponsorRequest',
            'uses' => 'SponsorController@postRequest',
        ]);

        // Modal Routes
        $router->get('modals/annotation_thanks', [
            'as'   => 'modals.thanks',
            'uses' => 'ModalController@getAnnotationThanksModal',
        ]);

        $router->post('modals/annotation_thanks', [
            'as'   => 'modals.thanks',
            'uses' => 'ModalController@seenAnnotationThanksModal',
        ]);

        //Annotation Routes
        $router->get('annotation/{annotation}', [
            'as'   => 'annotation.show',
            'uses' => 'AnnotationController@getIndex',
        ]);

        // Feed routes
        $router->get('feed', [
            'as'   => 'feed',
            'uses' => 'FeedController@getFeed'
        ]);
        $router->get('sitemap', [
            'as'   => 'sitemap',
            'uses' => 'FeedController@getSitemap'
        ]);
    }
}
