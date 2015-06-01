<?php

namespace MXAbierto\Participa\Http\Controllers;

class PageController extends AbstractController
{
    /**
     * Display the home page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getHome()
    {
        return view('page.index', [
            'page_id'    => 'home',
            'page_title' => 'gob.mx/participa',
        ]);
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAbout()
    {
        return view('page.index', [
            'page_id'    => 'about',
            'page_title' => 'gob.mx/participa - Acerca de',
        ]);
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFaq()
    {
        return view('page.index', [
            'page_id'    => 'faq',
            'page_title' => 'gob.mx/participa - Pregunstas Frecuentes',
        ]);
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPrivacy()
    {
        return view('page.index', [
            'page_id'    => 'privacy',
            'page_title' => 'gob.mx/participa - Privacidad',
        ]);
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTerms()
    {
        return view('page.index', [
            'page_id'    => 'terms',
            'page_title' => 'gob.mx/participa - Términos y condiciones',
        ]);
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCopyright()
    {
        return view('page.index', [
            'page_id'    => 'copyright',
            'page_title' => 'gob.mx/participa - Licencia',
        ]);
    }
}
