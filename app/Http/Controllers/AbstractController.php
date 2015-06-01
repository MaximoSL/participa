<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class AbstractController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
}
