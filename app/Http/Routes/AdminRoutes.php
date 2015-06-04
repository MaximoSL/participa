<?php

namespace MXAbierto\Participa\Http\Routes;

use Illuminate\Contracts\Routing\Registrar;

/**
 * This is the admin routes class.
 *
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class AdminRoutes
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
        $router->group(['namespace' => 'Admin', 'middleware' => ['auth', 'auth.role:Admin']], function (Registrar $router) {
            //Dashboard Routes
            $router->get('dashboard', [
                'as'   => 'dashboard',
                'uses' => 'DashboardController@getIndex',
            ]);
            $router->get('dashboard/notifications', [
                'as'   => 'dashboard.notifications',
                'uses' => 'NotificationsController@getNotifications',
            ]);
            $router->post('dashboard/notifications', [
                'as'   => 'dashboard.notifications',
                'uses' => 'NotificationsController@postNotifications',
            ]);

            //Dashboard's Doc Routes
            $router->get('dashboard/docs', [
                'as'   => 'dashboard.docs',
                'uses' => 'DocumentsController@getDocs',
            ]);
            $router->post('dashboard/docs', [
                'as'   => 'dashboard.docs',
                'uses' => 'DocumentsController@postDocs',
            ]);
            $router->get('dashboard/docs/{doc}', [
                'as'   => 'dashboard.docs.show',
                'uses' => 'DocumentsController@showDoc',
            ]);
        });
    }
}
