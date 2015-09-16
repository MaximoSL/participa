<?php

namespace MXAbierto\Participa\Presenters;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\Log;
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
                <div class="hidden-xs side-diff-visible">
                    <div class="row">
                        <div class="col-sm-6">
                        <h5>Código vigente</h5>
                        </div>
                        <div class="col-sm-6">
                        <h5>Código propuesto</h5>
                        </div>
                    </div>
                    <hr class="hidden-xs red">
                </div>
            ';
            foreach ($snippets as $key => $snippet) {
                $html .= '
                    <div class="row">
                        <div class="diff_layout" id="diff_layout_snippet_'.$key.'">
                            <div class="col-sm-12 title">
                                <div class="row">';

                         $html .= '<div class="col-sm-12 side-diff-visible hidden-xs">';

                        if (is_array($snippet['title'])) {
                            $html .= '
                                    '.$snippet['title'][2].'
                                    <div class="row">
                                        <div class="col-sm-6">
                                            '.$snippet['title'][0].'
                                        </div>
                                        <div class="col-sm-6">
                                            '.$snippet['title'][1].'
                                        </div>
                                    </div>
                            ';
                        } else {
                            $html .= $snippet['title'];
                        }

                        $html .= '
                                </div>
                        ';

                        $html .= '<div class="col-sm-12 inline-diff-visible hidden-xs">';

                        if (is_array($snippet['title'])) {
                            $html .= '
                                    '.$snippet['title'][2].'
                                    <p><b>Código vigente: </b> '.$snippet['title'][0].'</p>
                                    <p><b>Código propuesto: </b> '.$snippet['title'][1].'</p>
                            ';
                        } else {
                            $html .= $snippet['title'];
                        }

                        $html .= '</div>';

                        $html .= '<div class="col-sm-12 visible-xs">';

                        if (is_array($snippet['title'])) {
                            $html .= '
                                    '.$snippet['title'][2].'
                                    <p><b>Código vigente: </b> '.$snippet['title'][0].'</p>
                                    <p><b>Código propuesto: </b> '.$snippet['title'][1].'</p>
                            ';
                        } else {
                            $html .= $snippet['title'];
                        }

                        $html .= '</div>';

                    $html .= '</div>
                        </div>
                            <div class="col-sm-6 text1">
                                '.$snippet['current_content'].'
                            </div>
                            <div class="col-sm-6 text2">
                                '.$snippet['proposed_content'].'
                            </div>
                            <div class="col-sm-12 diff_result inline_diff_result" style="display:none;"></div>
                            <div class="col-sm-6 diff_result side_diff_result side_text_1" style="display:none;"></div>
                            <div class="col-sm-6 diff_result side_diff_result side_text_2" style="display:none;"></div>
                        </div>
                    </div>
                    <hr class="inline-diff-visible">
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
