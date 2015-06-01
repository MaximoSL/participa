<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Model\Model;

class AnnotationTag extends Model
{
    protected $table = "annotation_tags";
    protected $softDelete = true;
    protected $fillable = ['annotation_id', 'tag'];

    public function annotation()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\DBAnnotation');
    }
}
