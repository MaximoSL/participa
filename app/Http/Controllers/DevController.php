<?php

namespace MXAbierto\Participa\Http\Controllers;

class DevController extends AbstractController
{
    public function testEvent()
    {
        Event::fire(MadisonEvent::TEST, Auth::user());

        var_dump('HERE');
        exit;
    }
}
