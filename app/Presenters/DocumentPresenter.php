<?php

namespace MXAbierto\Participa\Presenters;

use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        $doc_layouts = $this->wrappedObject->categories()->where('kind', 'layout')->lists('name', 'id')->all();
        $doc_layouts = array_map('strtolower', $doc_layouts);

        if (in_array('leyes', $doc_layouts)) {
            $chapter_column = 1;
            $current_code_column = 2;
            $current_content_column = 3;
            $proposed_code_column = 4;
            $proposed_content_column = 5;

            $doc_content = $this->wrappedObject->content->content;
            if (strpos($doc_content,'CSV_SOURCE::') !== false) {
                $doc_content = Storage::get(str_replace('CSV_SOURCE::', '', $doc_content));
            }

            $content_lines = json_decode($doc_content);

            $skip = true;
            $snippets = [];
            $chapter = '';
            $current_code = '';
            $proposed_code = '';
            foreach($content_lines as $key => $value) {
                if ($skip) {
                    $skip = false;
                    continue;
                }

                $chapter = (empty($value[$chapter_column])) ? $chapter : $value[$chapter_column];
                $current_code = (empty($value[$current_code_column])) ? $current_code : $value[$current_code_column];
                $proposed_code = (empty($value[$proposed_code_column])) ? $proposed_code : $value[$proposed_code_column];
                $current_content = (empty($value[$current_content_column])) ? '' : $value[$current_content_column];
                $proposed_content = (empty($value[$proposed_content_column])) ? '' : $value[$proposed_content_column];

                $title = $chapter.'. '.$proposed_code;

                $snippets[] = [
                    'title' => $title,
                    'chapter' => $chapter,
                    'current_code' => $current_code,
                    'proposed_code' => $proposed_code,
                    'current_content' => $current_content,
                    'proposed_content' => $proposed_content,
                ];

            }

            $html = '';
            foreach($snippets as $snippet) {
                $html .= '<div class="show_diff_inline">
                    <div class="text1">
                        '.$snippet['current_content'].'
                    </div>
                    <div class="text2">
                        '.$proposed_content.'
                    </div>
                </div>';
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
