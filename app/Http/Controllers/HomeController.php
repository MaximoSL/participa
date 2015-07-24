<?php

namespace MXAbierto\Participa\Http\Controllers;

use MXAbierto\Participa\Models\Category;
use MXAbierto\Participa\Models\Status;

/**
 * The home controller class.
 *
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class HomeController extends AbstractController
{
    /**
     * Get docs index.
     *
     * @return \Illuminate\Http\Response
     */
    public function getHome()
    {
        $categories = Category::all();
        $statuses = Status::all();

        return view('page.index', [
            'page_id'    => 'home',
            'page_title' => 'gob.mx/'.config('app.base_name'),
            'categories' => $categories,
            'statuses'   => $statuses,
        ]);
    }
}
