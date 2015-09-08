<?php

namespace MXAbierto\Participa\Http\Controllers;

use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class AbstractController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     *  Helper function to return error as growl message.
     *
     *  @param string $message
     *  @param string $severity
     *
     *  @return array $growled
     *
     * @todo this should accept an array of messages / severities
     */
    protected function growlMessage($messages, $severity)
    {
        $growled = ['messages' => []];

        //If we've been passed an array of messages
        if (is_array($messages)) {

            //If we've only been passed one severity
            if (!is_array($severity)) {
                //Set that severity for every message
                foreach ($messages as $message) {
                    array_push($growled['messages'], ['text' => $message, 'severity' => $severity]);
                }
            } elseif (count($message) === count($severity)) { //Ensure we have the same number of messages <=> severities
                foreach ($messages as $index => $message) {
                    array_push($growled['messages'], ['text' => $message, 'severity' => $severity[$index]]);
                }
            } else { //Throw an exception if there's a mismatch
                throw new Exception('Unable to create growl message array because of size mismatches');
            }
        } else {
            array_push($growled['messages'], ['text' => $messages, 'severity' => $severity]);
        }

        return $growled;
    }

    protected function Utf8_ansi($text = '') {

        $utf8_ansi2 = array(
        "\u00c0" => "À",
        "\u00c1" => "Á",
        "\u00c2" => "Â",
        "\u00c3" => "Ã",
        "\u00c4" => "Ä",
        "\u00c5" => "Å",
        "\u00c6" => "Æ",
        "\u00c7" => "Ç",
        "\u00c8" => "È",
        "\u00c9" => "É",
        "\u00ca" => "Ê",
        "\u00cb" => "Ë",
        "\u00cc" => "Ì",
        "\u00cd" => "Í",
        "\u00ce" => "Î",
        "\u00cf" => "Ï",
        "\u00d1" => "Ñ",
        "\u00d2" => "Ò",
        "\u00d3" => "Ó",
        "\u00d4" => "Ô",
        "\u00d5" => "Õ",
        "\u00d6" => "Ö",
        "\u00d8" => "Ø",
        "\u00d9" => "Ù",
        "\u00da" => "Ú",
        "\u00db" => "Û",
        "\u00dc" => "Ü",
        "\u00dd" => "Ý",
        "\u00df" => "ß",
        "\u00e0" => "à",
        "\u00e1" => "á",
        "\u00e2" => "â",
        "\u00e3" => "ã",
        "\u00e4" => "ä",
        "\u00e5" => "å",
        "\u00e6" => "æ",
        "\u00e7" => "ç",
        "\u00e8" => "è",
        "\u00e9" => "é",
        "\u00ea" => "ê",
        "\u00eb" => "ë",
        "\u00ec" => "ì",
        "\u00ed" => "í",
        "\u00ee" => "î",
        "\u00ef" => "ï",
        "\u00f0" => "ð",
        "\u00f1" => "ñ",
        "\u00f2" => "ò",
        "\u00f3" => "ó",
        "\u00f4" => "ô",
        "\u00f5" => "õ",
        "\u00f6" => "ö",
        "\u00f8" => "ø",
        "\u00f9" => "ù",
        "\u00fa" => "ú",
        "\u00fb" => "û",
        "\u00fc" => "ü",
        "\u00fd" => "ý",
        "\u00ff" => "ÿ");

        return strtr($text, $utf8_ansi2);

    }
}
