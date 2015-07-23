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
        $categories = Category::where('kind', 'category')->get();
        $institutions = Category::where('kind', 'institution')->get();
        $statuses = Status::all();

        return view('page.index', [
            'page_id'      => 'home',
            'page_title'   => 'gob.mx/'.config('app.base_name'),
            'categories'   => $categories,
            'institutions' => $institutions,
            'statuses'     => $statuses,
        ]);
    }
}
