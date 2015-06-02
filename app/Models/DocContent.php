<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocContent extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doc_contents';

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
