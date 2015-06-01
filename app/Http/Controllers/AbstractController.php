<?php

namespace MXAbierto\Participa\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class AbstractController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
}
