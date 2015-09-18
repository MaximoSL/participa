<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 	Document date model.
 */
class Date extends Model
{
    //Document this meta is describing

    public function docs()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\Doc');
    }
}
