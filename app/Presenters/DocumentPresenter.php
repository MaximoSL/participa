<?php

namespace MXAbierto\Participa\Presenters;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\Log;
use McCool\LaravelAutoPresenter\BasePresenter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
        if (!$this->wrappedObject->content) {
            $empty_content_log = new Logger('Documento sin contenido');
            $empty_content_log->pushHandler(new StreamHandler(storage_path().'/logs/empty_content_log.log', Logger::INFO));
            $empty_content_log->addInfo('El documento '.$this->wrappedObject->id.' - '.$this->wrappedObject->title.', no tiene registro relacionado en la tabla doc_contents');

            return;
        }

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
