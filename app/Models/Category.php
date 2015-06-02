<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 	Document meta model.
 */
class Category extends Model
{
    /**
     * The doc relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function docs()
    {
        return $this->belongsToMany('MXAbierto\Participa\Models\Doc');
    }
}
