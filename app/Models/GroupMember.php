<?php

namespace MXAbierto\Participa\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    public static $timestamp = true;
    protected $softDelete = true;

    public function user()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\User');
    }

    public function group()
    {
        return $this->belongsTo('MXAbierto\Participa\Models\Group');
    }

    public static function findByGroupId($groupId)
    {
        return static::where('group_id', '=', $groupId)->get();
    }

    public function getUserName()
    {
        $user = User::where('id', '=', $this->user_id)->first();

        if (!$user) {
            throw new \Exception("Could not locate user with ID");
        }

        return "{$user->fname} {$user->lname}";
    }
}
