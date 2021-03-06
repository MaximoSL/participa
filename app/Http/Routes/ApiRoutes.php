<?php

namespace MXAbierto\Participa\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

/**
 * This is the api routes class.
 *
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class ApiRoutes
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
        $router->group(['namespace' => 'Api', 'prefix' => 'api'], function (Registrar $router) {
            //Annotation Action Routes
            $router->post('docs/{doc}/annotations/{annotation}/likes', 'AnnotationController@postLikes');
            $router->post('docs/{doc}/annotations/{annotation}/dislikes', 'AnnotationController@postDislikes');
            $router->post('docs/{doc}/annotations/{annotation}/flags', 'AnnotationController@postFlags');
            $router->post('docs/{doc}/annotations/{annotation}/seen', 'AnnotationController@postSeen');
            $router->get('docs/{doc}/annotations/{annotation}/likes', 'AnnotationController@getLikes');
            $router->get('docs/{doc}/annotations/{annotation}/dislikes', 'AnnotationController@getDislikes');
            $router->get('docs/{doc}/annotations/{annotation}/flags', 'AnnotationController@getFlags');

            //Annotation Comment Routes
            $router->get('docs/{doc}/annotations/{annotation}/comments', 'AnnotationController@getComments');
            $router->post('docs/{doc}/annotations/{annotation}/comments', 'AnnotationController@postComments');
            $router->get('docs/{doc}/annotations/{annotation}/comments/{comment}', 'AnnotationController@getComments');

            //Annotation Routes
            $router->get('annotations/search', 'AnnotationController@getSearch');
            $router->get('docs/{doc}/annotations/{annotation?}', [
                'as'   => 'getAnnotation',
                'uses' => 'AnnotationController@getIndex',
            ]);
            $router->post('docs/{doc}/annotations', 'AnnotationController@postIndex');
            $router->put('docs/{doc}/annotations/{annotation}', 'AnnotationController@putIndex');
            $router->delete('docs/{doc}/annotations/{annotation}', 'AnnotationController@deleteIndex');

            //Document Comment Routes
            $router->post('docs/{doc}/comments', 'CommentController@postIndex');
            $router->get('docs/{doc}/comments', 'CommentController@getIndex');
            $router->get('docs/{doc}/comments/{comment?}', 'CommentController@getIndex');
            $router->post('docs/{doc}/comments/{comment}/likes', 'CommentController@postLikes');
            $router->post('docs/{doc}/comments/{comment}/dislikes', 'CommentController@postDislikes');
            $router->post('docs/{doc}/comments/{comment}/flags', 'CommentController@postFlags');
            $router->post('docs/{doc}/comments/{comment}/comments', 'CommentController@postComments');
            $router->post('docs/{doc}/comments/{comment}/seen', 'CommentController@postSeen');
            $router->post('docs/{doc}/comments/{comment}/hide', [
                'as'   => 'comment/hide',
                'uses' => 'CommentController@destroy',
            ]);
            $router->delete('docs/{doc}/comments/{comment}/delete', [
                'as'   => 'comment/delete',
                'uses' => 'CommentController@destroy',
            ]);

            //Document Support / Oppose routes
            $router->post('users/support/{doc}', 'UserController@postSupport');
            $router->get('users/support/{doc}', 'UserController@getSupport');

            //Document Api Routes
            $router->get('docs/recent/{query?}', 'DocumentController@getRecent')->where('query', '[0-9]+');
            $router->get('docs/categories', 'DocumentController@getCategories');
            $router->get('docs/statuses', 'DocumentController@getAllStatuses');
            $router->get('docs/sponsors', 'DocumentController@getAllSponsors');
            $router->get('docs/groups', [
                'as'   => 'docs.getAllGroups',
                'uses' => 'DocumentController@getAllGroups',
            ]);
            $router->get('docs/{doc}/categories', 'DocumentController@getCategories');
            $router->post('docs/{doc}/categories', 'DocumentController@postCategories');
            $router->get('docs/{doc}/introtext', 'DocumentController@getIntroText');
            $router->post('docs/{doc}/introtext', 'DocumentController@postIntroText');
            $router->get('docs/{doc}/sponsor/{sponsor}', 'DocumentController@hasSponsor');
            $router->get('docs/{doc}/sponsor', 'DocumentController@getSponsor');
            $router->post('docs/{doc}/sponsor', 'DocumentController@postSponsor');
            $router->get('docs/{doc}/group', 'DocumentController@getGroup');
            $router->post('docs/{doc}/group', 'DocumentController@postGroup');
            $router->get('docs/{doc}/status', 'DocumentController@getStatus');
            $router->post('docs/{doc}/status', 'DocumentController@postStatus');
            $router->get('docs/{doc}/dates', 'DocumentController@getDates');
            $router->post('docs/{doc}/dates', 'DocumentController@postDate');
            $router->put('dates/{date}', 'DocumentController@putDate');
            $router->delete('docs/{doc}/dates/{date}', 'DocumentController@deleteDate');
            $router->get('docs/{doc}', 'DocumentController@getDoc');
            $router->post('docs/{doc}/title', 'DocumentController@postTitle');
            $router->post('docs/{doc}/slug', 'DocumentController@postSlug');
            $router->post('docs/{doc}/content', 'DocumentController@postContent');
            $router->get('docs', 'DocumentController@getDocs');

            //User Routes
            $router->get('user/current', 'UserController@getCurrent');
            $router->get('user/verify', 'UserController@getVerify');
            $router->post('user/verify', 'UserController@postVerify');
            $router->get('user/admin', 'UserController@getAdmins');
            $router->post('user/admin', 'UserController@postAdmin');
            $router->get('user/{user}', 'UserController@getUser');
            $router->get('user/independent/verify', 'UserController@getIndependentVerify');
            $router->post('user/independent/verify', 'UserController@postIndependentVerify');
            $router->put('user/{user}/edit/email', 'UserController@editEmail');
            $router->get('user/{user}/notifications', 'UserController@getNotifications');
            $router->put('user/{user}/notifications', 'UserController@putNotifications');
        });

        // This fixes the back button on ajax requests
        $router->get('api/auth/login', [
            'as'   => 'api.auth.login',
            'uses' => 'AuthController@getLogin',
        ]);
        $router->post('api/auth/login', [
            'as'   => 'api.auth.login',
            'uses' => 'AuthController@postLogin',
        ]);
        $router->get('api/logout', [
            'as'   => 'api.auth.logout',
            'uses' => 'AuthController@getLogout',
        ]);
        $router->get('api/auth/signup', [
            'as'   => 'api.auth.signup',
            'uses' => 'AuthController@getSignup',
        ]);
        $router->post('api/auth/signup', [
            'as'   => 'api.auth.signup',
            'uses' => 'AuthController@postSignup',
        ]);
    }
}
