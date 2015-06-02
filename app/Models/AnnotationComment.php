<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnotationComment extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'annotation_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'annotation_id', 'text'];

    public $incrementing = false;

    public function annotation()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\DBAnnotation');
    }

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }

  /**
   * Created direct link for this AnnotationComment.
   *
   * @param int $doc_id
   *
   * @return URL::to()
   */
  public function getLink($doc_id)
  {
      $slug = \DB::table('docs')->where('id', $doc_id)->pluck('slug');

      return route('docs.doc', $slug).'#annsubcomment_'.$this->id;
  }
}
