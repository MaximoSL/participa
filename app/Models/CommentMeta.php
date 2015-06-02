<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 	Comment meta model.
 */
class CommentMeta extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'comment_meta';

    const TYPE_USER_ACTION = 'user_action';

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }

    public function parent()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\Comment');
    }
}
