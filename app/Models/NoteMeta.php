<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 	Note meta model.
 */
class NoteMeta extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'note_meta';

    const TYPE_USER_ACTION = 'user_action';

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }
}
