<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

class DocContent extends Model
{
    protected $table = 'doc_contents';
    protected $softDelete = true;

    public function doc()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\Doc');
    }

    public function notes()
    {
        return $this->hasMany('MXAbierto\Participa\Models\Note', 'section_id');
    }

    public function content_children()
    {
        return $this->hasMany('MXAbierto\Participa\Models\DocContent', 'parent_id');
    }

    public function content_parent()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\DocContent', 'parent_id');
    }

    public function html()
    {
        return Markdown::render($this->content);
    }
}
