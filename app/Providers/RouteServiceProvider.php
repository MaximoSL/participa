<?php

namespace MXAbierto\Participa\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'MXAbierto\Participa\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        $this->registerBindings();
    }

    /**
     * Register model bindings.
     *
     * @return void
     */
    protected function registerBindings()
    {
        $this->app->router->model('user', 'MXAbierto\Participa\Models\User');
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function (Router $router) {
            foreach (glob(app_path('Http//Routes').'/*.php') as $file) {
                $this->app->make('MXAbierto\\Participa\\Http\\Routes\\'.basename($file, '.php'))->map($router);
            }
        });
    }
}
