<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 	Document meta model.
 */
class Status extends Model
{
    //Document this meta is describing

    public function docs()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\Doc');
    }
}
