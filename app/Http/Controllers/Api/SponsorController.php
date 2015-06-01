<?php

namespace MXAbierto\Participa\Http\Controllers\Api;

use MXAbierto\Participa\Models\Doc;

class SponsorController extends AbstractApiController
{
    public function getAllSponsors()
    {
        $results = Doc::getAllValidSponsors();

        return response()->json($results);
    }
}
