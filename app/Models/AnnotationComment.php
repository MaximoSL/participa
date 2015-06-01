<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Model\Model;

class AnnotationComment extends Model
{
    protected $table = "annotation_comments";
    protected $softDelete = true;
    public $incrementing = false;
    protected $fillable = ['id', 'user_id', 'annotation_id', 'text'];

    public function annotation()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\DBAnnotation');
    }

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }

  /**
   * getLink
   *   Created direct link for this AnnotationComment.
   *
   * @param int $doc_id
   *
   * @return URL::to()
   */
  public function getLink($doc_id)
  {
      $slug = DB::table('docs')->where('id', $doc_id)->pluck('slug');

      return URL::to('docs/'.$slug.'#annsubcomment_'.$this->id);
  }
}
