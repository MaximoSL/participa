<?php

namespace MXAbierto\Participa\Presenters;

use GrahamCampbell\Markdown\Facades\Markdown;
use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * The document presenter class.
 *
 * @author Joseph Cohen <joseph.cohen@dinkbit.com>
 */
class DocumentPresenter extends BasePresenter
{
    /**
     * Renders the content from Markdown into HTML.
     *
     * @return string
     */
    public function formatted_content()
    {
        return Markdown::convertToHtml($this->wrappedObject->content->content);
    }

    /**
     * Get the group name.
     *
     * @return string
     */
    public function group_name()
    {
        $group = $this->wrappedObject->group;

        if (!$group || $group->isEmpty()) {
            return;
        }

        return $this->wrappedObject->group->first()->name;
    }
}
