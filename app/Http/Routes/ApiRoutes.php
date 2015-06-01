<?php

namespace MXAbierto\Participa\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

/**
 * This is the main routes class.
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
        $router->group(['namespace' => 'Api', 'prefix' => 'api'], function(Registrar $router) {
            $router->get('api/user/sponsors/all', 'DocumentApiController@getAllSponsorsForUser');
            $router->get('api/sponsors/all', 'SponsorApiController@getAllSponsors');

            //Annotation Action Routes
            $router->post('api/docs/{doc}/annotations/{annotation}/likes', 'AnnotationApiController@postLikes');
            $router->post('api/docs/{doc}/annotations/{annotation}/dislikes', 'AnnotationApiController@postDislikes');
            $router->post('api/docs/{doc}/annotations/{annotation}/flags', 'AnnotationApiController@postFlags');
            $router->post('api/docs/{doc}/annotations/{annotation}/seen', 'AnnotationApiController@postSeen');
            $router->get('api/docs/{doc}/annotations/{annotation}/likes', 'AnnotationApiController@getLikes');
            $router->get('api/docs/{doc}/annotations/{annotation}/dislikes', 'AnnotationApiController@getDislikes');
            $router->get('api/docs/{doc}/annotations/{annotation}/flags', 'AnnotationApiController@getFlags');

            //Annotation Comment Routes
            $router->get('api/docs/{doc}/annotations/{annotation}/comments', 'AnnotationApiController@getComments');
            $router->post('api/docs/{doc}/annotations/{annotation}/comments', 'AnnotationApiController@postComments');
            $router->get('api/docs/{doc}/annotations/{annotation}/comments/{comment}', 'AnnotationApiController@getComments');

            //Annotation Routes
            $router->get('api/annotations/search', 'AnnotationApiController@getSearch');
            $router->get('api/docs/{doc}/annotations/{annotation?}', ['as' => 'getAnnotation', 'uses' => 'AnnotationApiController@getIndex']);
            $router->post('api/docs/{doc}/annotations', 'AnnotationApiController@postIndex');
            $router->put('api/docs/{doc}/annotations/{annotation}', 'AnnotationApiController@putIndex');
            $router->delete('api/docs/{doc}/annotations/{annotation}', 'AnnotationApiController@deleteIndex');

            //Document Comment Routes
            $router->post('api/docs/{doc}/comments', 'CommentApiController@postIndex');
            $router->get('api/docs/{doc}/comments', 'CommentApiController@getIndex');
            $router->get('api/docs/{doc}/comments/{comment?}', 'CommentApiController@getIndex');
            $router->post('api/docs/{doc}/comments/{comment}/likes', 'CommentApiController@postLikes');
            $router->post('api/docs/{doc}/comments/{comment}/dislikes', 'CommentApiController@postDislikes');
            $router->post('api/docs/{doc}/comments/{comment}/flags', 'CommentApiController@postFlags');
            $router->post('api/docs/{doc}/comments/{comment}/comments', 'CommentApiController@postComments');
            $router->post('api/docs/{doc}/comments/{comment}/seen', 'CommentApiController@postSeen');
            $router->post('api/docs/{doc}/comments/{comment}/hide', [
                'as'   => 'comment/hide',
                'uses' => 'CommentApiController@destroy'
            ]);
            $router->delete('api/docs/{doc}/comments/{comment}/delete', [
                'as'   => 'comment/delete',
                'uses' => 'CommentApiController@destroy'
            ]);

            //Document Support / Oppose routes
            $router->post('api/docs/{doc}/support/', 'DocController@postSupport');
            $router->get('api/users/{user}/support/{doc}', 'UserApiController@getSupport');

            //Document Api Routes
            $router->get('api/docs/recent/{query?}', 'DocumentApiController@getRecent')->where('query', '[0-9]+');
            $router->get('api/docs/categories', 'DocumentApiController@getCategories');
            $router->get('api/docs/statuses', 'DocumentApiController@getAllStatuses');
            $router->get('api/docs/sponsors', 'DocumentApiController@getAllSponsors');
            $router->get('api/docs/{doc}/categories', 'DocumentApiController@getCategories');
            $router->post('api/docs/{doc}/categories', 'DocumentApiController@postCategories');
            $router->get('api/docs/{doc}/introtext', 'DocumentApiController@getIntroText');
            $router->post('api/docs/{doc}/introtext', 'DocumentApiController@postIntroText');
            $router->get('api/docs/{doc}/sponsor/{sponsor}', 'DocumentApiController@hasSponsor');
            $router->get('api/docs/{doc}/sponsor', 'DocumentApiController@getSponsor');
            $router->post('api/docs/{doc}/sponsor', 'DocumentApiController@postSponsor');
            $router->get('api/docs/{doc}/status', 'DocumentApiController@getStatus');
            $router->post('api/docs/{doc}/status', 'DocumentApiController@postStatus');
            $router->get('api/docs/{doc}/dates', 'DocumentApiController@getDates');
            $router->post('api/docs/{doc}/dates', 'DocumentApiController@postDate');
            $router->put('api/dates/{date}', 'DocumentApiController@putDate');
            $router->delete('api/docs/{doc}/dates/{date}', 'DocumentApiController@deleteDate');
            $router->get('api/docs/{doc}', 'DocumentApiController@getDoc');
            $router->post('api/docs/{doc}/title', 'DocumentApiController@postTitle');
            $router->post('api/docs/{doc}/slug', 'DocumentApiController@postSlug');
            $router->post('api/docs/{doc}/content', 'DocumentApiController@postContent');
            $router->get('api/docs/', 'DocumentApiController@getDocs');

            //User Routes
            $router->get('api/user/{user}', 'UserApiController@getUser');
            $router->get('api/user/verify/', 'UserApiController@getVerify');
            $router->post('api/user/verify/', 'UserApiController@postVerify');
            $router->get('api/user/admin/', 'UserApiController@getAdmins');
            $router->post('api/user/admin/', 'UserApiController@postAdmin');
            $router->get('api/user/independent/verify/', 'UserApiController@getIndependentVerify');
            $router->post('api/user/independent/verify/', 'UserApiController@postIndependentVerify');
            $router->get('api/user/current', 'UserController@getCurrent');
            $router->put('api/user/{user}/edit/email', 'UserController@editEmail');
            $router->get('api/user/{user}/notifications', 'UserController@getNotifications');
            $router->put('api/user/{user}/notifications', 'UserController@putNotifications');

            // Group Routes
            $router->get('api/groups/verify/', 'GroupsApiController@getVerify');
            $router->post('api/groups/verify/', 'GroupsApiController@postVerify');

            // User Login / Signup AJAX requests
            $router->get('api/user/login', ['as' => 'api/user/login', 'uses' => 'UserManageApiController@getLogin']);
            $router->post('api/user/login', ['as' => 'api/user/login', 'uses' => 'UserManageApiController@postLogin']);
            $router->get('api/user/signup', ['as' => 'api/user/signup', 'uses' => 'UserManageApiController@getSignup']);
            $router->post('api/user/signup', ['as' => 'api/user/signup', 'uses' => 'UserManageApiController@postSignup']);
        });
    }
}
