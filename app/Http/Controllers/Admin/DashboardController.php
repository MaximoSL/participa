<?php

namespace MXAbierto\Participa\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use MXAbierto\Participa\Http\Controllers\AbstractController;

/**
 * 	Controller for admin dashboard.
 */
class DashboardController extends AbstractController
{
    /**
     * Dashboard Index View.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex()
    {
        return view('dashboard.index', [
            'page_id'        => 'dashboard',
            'page_title'     => 'Dashboard',
        ]);
    }
}
