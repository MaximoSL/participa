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
            'uses' => 'PageController@getHome',
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
        //$router->controller('user', 'UserController');
        $router->get('auth/login', [
            'as'   => 'auth.login',
            'uses' => 'AuthController@getLogin',
        ]);
        $router->post('auth/login', [
            'as'   => 'auth.login',
            'uses' => 'AuthController@postLogin',
        ]);
        $router->get('logout', [
            'as'   => 'logout',
            'uses' => 'AuthController@getLogout',
        ]);
        $router->get('auth/signup', [
            'as'   => 'auth.signup',
            'uses' => 'AuthController@getSignup',
        ]);
        $router->post('auth/signup', [
            'as'   => 'user.signup',
            'uses' => 'AuthController@postSignup',
        ]);

        $router->get('user/{user}', [
            'as'   => 'user/index',
            'uses' => 'UserController@getIndex',
        ]);
        $router->get('user/edit/{user}', [
            'as'   => 'user.edit',
            'uses' => 'UserController@getEdit',
        ]);
        $router->put('user/edit/{user}', [
            'as'   => 'user.edit',
            'uses' => 'UserController@putEdit',
        ]);
        $router->get('user/edit/{user}/notifications', [
            'as'   => 'user.edit.notifications',
            'uses' => 'UserController@editNotifications',
        ]);

        //Password Routes
        $router->get('password/remind', [
            'as'   => 'password/remind',
            'uses' => 'RemindersController@getRemind',
        ]);
        $router->post('password/remind', 'RemindersController@postRemind');
        $router->get('password/reset/{token}',  'RemindersController@getReset');
        $router->post('password/reset',  'RemindersController@postReset');

        // Confirmation email resend
        $router->get('verification/remind',  [
            'as'   => 'verification/remind',
            'uses' => 'RemindersController@getConfirmation',
        ]);
        $router->post('verification/remind', [
            'as'   => 'verification/remind',
            'uses' => 'RemindersController@postConfirmation',
        ]);

        // User Groups Routes
        $router->get('groups', [
            'as'   => 'groups',
            'uses' => 'GroupsController@getIndex',
        ]);
        $router->get('groups/new', [
            'as'   => 'groups.new',
            'uses' => 'GroupsController@getNew',
        ]);
        $router->post('groups/new', [
            'as'   => 'groups.new',
            'uses' => 'GroupsController@postNew',
        ]);
        $router->put('groups/edit', [
            'as'   => 'groups.edit',
            'uses' => 'GroupsController@putEdit',
        ]);
        $router->get('groups/edit/{groupId?}', [
            'as'   => 'groups.edit',
            'uses' => 'GroupsController@getEdit',
        ]);
        $router->get('groups/members/{groupId}', [
            'as'   => 'groups.members',
            'uses' => 'GroupsController@getMembers',
        ]);
        $router->get('groups/member/{memberId}/delete', 'GroupsController@removeMember');
        $router->post('groups/member/{memberId}/role', 'GroupsController@changeMemberRole');
        $router->get('groups/invite/{groupId}', 'GroupsController@inviteMember');
        $router->put('groups/invite/{groupId}', 'GroupsController@processMemberInvite');
        $router->get('groups/active/{groupId}', 'GroupsController@setActiveGroup');

        // Document Routes
        $router->get('docs', [
            'as'   => 'docs',
            'uses' => 'DocController@index',
        ]);
        $router->get('docs/{slug}', [
            'as'   => 'docs.doc',
            'uses' => 'DocController@getDoc',
        ]);
        $router->get('docs/embed/{slug}', 'DocController@getEmbedded');
        $router->get('docs/{slug}/feed', 'DocController@getFeed');
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

        //Dashboard Routes
        //$router->controller('dashboard', 'DashboardController');
        $router->get('dashboard', [
            'as'   => 'dashboard',
            'uses' => 'DashboardController@getIndex',
        ]);

        //Dashboard's Doc Routes
        $router->get('dashboard/docs', [
            'as'   => 'dashboard/docs',
            'uses' => 'DashboardController@getDocs',
        ]);
        $router->post('dashboard/docs', [
            'as'   => 'dashboard/docs',
            'uses' => 'DashboardController@postDocs',
        ]);
        $router->get('dashboard/docs/{doc}', [
            'as'   => 'dashboardShowsDoc',
            'uses' => 'DashboardController@getDocs',
        ]);

        // // Modal Routes
        // $router->get('modals/annotation_thanks', [
        //     'uses'   => 'ModalController@getAnnotationThanksModal',
        //     'before' => 'disable profiler',
        // ]);
        //
        // $router->post('modals/annotation_thanks', 'ModalController@seenAnnotationThanksModal');
        //
        //
        // //Annotation Routes
        // $router->get('annotation/{annotation}', 'AnnotationController@getIndex');
        //
        //
        //
        // $router->get('docs/feed', [
        //     'as'   => 'dashboardShowsDoc',
        //     'uses' => 'DashboardController@getDocs'
        // ]);
        //
        // $router->get('sitemap', [
        //     'as'   => 'dashboardShowsDoc',
        //     'uses' => 'DashboardController@getDocs'
        // ]);
    }
}
