<?php

namespace MXAbierto\Participa\Providers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @param \Illuminate\Contracts\View\Factory $view
     *
     * @return void
     */
    public function boot(ViewFactory $view)
    {
        $view->composer('*', 'MXAbierto\Participa\Composers\LoggedUserComposer');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
