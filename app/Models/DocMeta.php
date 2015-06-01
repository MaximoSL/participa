<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Model\Model;

/**
 * 	Document meta model.
 */
class DocMeta extends Model
{
    protected $table = 'doc_meta';

    protected $softDelete = true;
    public static $timestamp = true;

    //Document this meta is describing
    public function doc()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\Doc');
    }

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }
}
