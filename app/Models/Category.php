<?php

namespace MXAbierto\Participa\Models;

/**
 * 	Document meta model.
 */
class Category extends Model
{
    //Document this meta is describing
    public function docs()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\Doc');
    }
}
