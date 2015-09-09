<?php

namespace MXAbierto\Participa\Presenters;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use McCool\LaravelAutoPresenter\BasePresenter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MXAbierto\Participa\Services\CSVParser;

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

        $doc_layouts = $this->wrappedObject->layouts_list;

        if (in_array('leyes', $doc_layouts)) {

            $csv_parser = new CSVParser();

            $snippets = $csv_parser->parseCSVContentForLawLayout($this->wrappedObject->content->content);

            $html = '';
            $html .= '
                <div class="row hidden-xs">
                    <div class="col-sm-6">
                    <h5>Descripción vigente</h5>
                    </div>
                    <div class="col-sm-6">
                    <h5>Descripción propuesta</h5>
                    </div>
                </div>
                <hr class="hidden-xs red">
            ';
            foreach ($snippets as $snippet) {
                $html .= '
                    <div class="row">
                        <div class="show_diff_inline">
                            <div class="col-sm-12 title">
                                '.$snippet['title'].'
                            </div>
                            <div class="col-sm-6 text1">
                                '.$snippet['current_content'].'
                            </div>
                            <div class="col-sm-6 text2">
                                '.$snippet['proposed_content'].'
                            </div>
                            <div class="col-sm-12 diff_result"></div>
                        </div>
                    </div>
                    <br><br>
                ';
            }

            return $html;
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
