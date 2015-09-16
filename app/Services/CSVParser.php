<?php

namespace MXAbierto\Participa\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CSVParser
{
    /**
     * Laravel config instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    protected $chapter_column = -1;

    protected $current_code_column = 0;

    protected $current_content_column = 1;

    protected $proposed_code_column = 2;

    protected $proposed_content_column = 3;

    public function setChapterColumn($value)
    {
        return $this->chapter_column = $value;
    }

    public function getChapterColumn()
    {
        return $this->chapter_column;
    }

    public function setCurrentCodeColumn($value)
    {
        return $this->current_code_column = $value;
    }

    public function getCurrentCodeColumn()
    {
        return $this->current_code_column;
    }

    public function setCurrentContentcolumn($value)
    {
        return $this->current_content_column = $value;
    }

    public function getCurrentContentcolumn()
    {
        return $this->current_content_column;
    }

    public function setProposedCodeColumn($value)
    {
        return $this->proposed_code_column = $value;
    }

    public function getProposedCodeColumn()
    {
        return $this->proposed_code_column;
    }

    public function setProposedContentcolumn($value)
    {
        return $this->proposed_content_column = $value;
    }

    public function getProposedContentcolumn()
    {
        return $this->proposed_content_column;
    }

    public static function processCSVFileContent(UploadedFile $file, $filename_prefix = null)
    {
        $content = json_encode(array_map('str_getcsv', file($file->getRealPath())));
        $content = Utilities::Utf8_ansi($content);
        if (strlen($content) > 65535) {
            $filename = uniqid($filename_prefix, true).'.txt';
            Storage::put('documents/csv/'.$filename, $content);
            $content = 'CSV_SOURCE::documents/csv/'.$filename;
        }

        return $content;
    }

    public function parseCSVContentForLawLayout($content)
    {
        if (strpos($content, 'CSV_SOURCE::') !== false) {
            $content = Storage::get(str_replace('CSV_SOURCE::', '', $content));
        }

        $content_rows = json_decode($content);

        $skip = true;
        $snippets = [];
        $chapter = '';
        $current_code = '';
        $proposed_code = '';

        foreach ($content_rows as $key => $row) {
            if ($skip || empty($row[$this->current_code_column])) {
                $skip = false;
                continue;
            }

            $chapter = (empty($row[$this->chapter_column])) ? $chapter : $row[$this->chapter_column].'. ';
            $current_code = (empty($row[$this->current_code_column])) ? $current_code : $row[$this->current_code_column];
            $proposed_code = (empty($row[$this->proposed_code_column])) ? $proposed_code : $row[$this->proposed_code_column];
            $current_content = (empty($row[$this->current_content_column])) ? '' : $row[$this->current_content_column];
            $proposed_content = (empty($row[$this->proposed_content_column])) ? '' : $row[$this->proposed_content_column];

            $title = $chapter.$proposed_code;

            if (!empty($current_code) && !empty($proposed_code)) {
                $title = [
                    $current_code,
                    $proposed_code,
                    $chapter,
                ];
            }

            $snippets[] = [
                'title'            => $title,
                'chapter'          => $chapter,
                'current_code'     => $current_code,
                'proposed_code'    => $proposed_code,
                'current_content'  => $current_content,
                'proposed_content' => $proposed_content,
            ];
        }

        return $snippets;
    }
}
