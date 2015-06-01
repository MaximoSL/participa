<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Model\Model;

/**
 * 	Organization Model.
 */
class Organization extends Model
{
    public static $timestamp = true;
    protected $softDelete = true;

    //Users belonging to this organization
    public function users()
    {
        return $this->has_many('User');
    }
}
