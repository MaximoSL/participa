<?php

namespace MXAbierto\Participa\Models;

class MadisonEvent
{
    //Admin Notifications
    const NEW_USER_SIGNUP = 'madison.user.signup';
    const DOC_EDITED = 'madison.doc.edited';
    const DOC_COMMENTED = 'madison.doc.commented';
    const DOC_ANNOTATED = 'madison.doc.annotated';
    const DOC_COMMENT_COMMENTED = 'madison.doc.comment.commented';
    const VERIFY_REQUEST_ADMIN = 'madison.verification.admin';
    const VERIFY_REQUEST_GROUP = 'madison.verification.group';
    const VERIFY_REQUEST_USER = 'madison.verification.user';
    const NEW_DOCUMENT = 'madison.doc.new';

    //User Notifications
    const NEW_GROUP_DOCUMENT = 'madison.user.new_group_doc';
    const NEW_DOC_VERSION = 'madison.user.new_doc_version';
    const NEW_ACTIVITY_VOTE = 'madison.user.new_activity_vote';
    const NEW_ACTIVITY_COMMENT = 'madison.user.new_activity_comment';

    //Other Events
    const DOC_SUBCOMMENT = 'madison.doc.subcomment';

    /*
    *	Return viable admin notifications
    *
    *	@param void
    *	@return array
    */

    public static function validAdminNotifications()
    {
        return [
            static::DOC_COMMENT_COMMENTED => trans('messages.commentoncomment'),
            static::DOC_COMMENTED         => trans('messages.commentondocument'),
            static::DOC_ANNOTATED         => trans('messages.commentondocannotated'),
            static::DOC_EDITED            => trans('messages.documentedited'),
            static::NEW_DOCUMENT          => trans('messages.newdoccreated'),
            static::NEW_USER_SIGNUP       => trans('messages.newusersignsup'),
            static::VERIFY_REQUEST_ADMIN  => trans('messages.newadminverifreq'),
            static::VERIFY_REQUEST_GROUP  => trans('messages.newgroupverifreq'),
            static::VERIFY_REQUEST_USER   => trans('messages.newindieverifreq'),
        ];
    }

    /*
    *	Return viable user notifications
    *
    *	@param void
    *	@return array
    */

    public static function validUserNotifications()
    {
        return [
            static::NEW_GROUP_DOCUMENT => trans('messages.newgroupdoc'),
            //static::NEW_DOC_VERSION => "When a new version of a document is posted that a user has interacted with",
            static::NEW_ACTIVITY_VOTE    => trans('messages.votepost'),
            static::NEW_ACTIVITY_COMMENT => trans('messages.commentactivity'),
        ];
    }
}
