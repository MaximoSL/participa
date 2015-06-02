<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 	Organization Model.
 */
class Organization extends Model
{
    use SoftDeletes;

    //Users belonging to this organization
    public function users()
    {
        return $this->has_many('User');
    }
}
