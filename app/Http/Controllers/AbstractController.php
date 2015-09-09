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
}
