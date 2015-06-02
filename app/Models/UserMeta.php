<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 	User meta model.
 */
class UserMeta extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_meta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'meta_key'];

    const TYPE_SEEN_ANNOTATION_THANKS = 'seen_annotation_thanks';
    const TYPE_INDEPENDENT_SPONSOR = 'independent_sponsor';

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }
}
