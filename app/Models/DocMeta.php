<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 	Document meta model.
 */
class DocMeta extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doc_meta';

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
