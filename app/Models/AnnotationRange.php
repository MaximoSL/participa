<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnotationRange extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'annotation_ranges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['start', 'end', 'start_offset', 'end_offset'];

    public $incrementing = false;

    public function annotation()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\DBAnnotation');
    }

    public static function firstByRangeOrNew(array $input)
    {
        $retval = static::where('annotation_id', '=', $input['annotation_id'])
                        ->where('start_offset', '=', $input['start_offset'])
                        ->where('end_offset', '=', $input['end_offset'])
                        ->first();

        if (!$retval) {
            $retval = new static();

            foreach ($input as $key => $val) {
                $retval->$key = $val;
            }
        }

        return $retval;
    }
}
