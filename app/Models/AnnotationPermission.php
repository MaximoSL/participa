<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

class AnnotationPermission extends Model
{
    protected $table = "annotation_permissions";
    protected $softDelete = true;
    protected $fillable = ['annotation_id', 'user_id'];

    public function annotation()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\DBAnnotation');
    }
}
