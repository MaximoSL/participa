<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => '',
        'secret' => '',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => 'MXAbierto\Participa\User',
        'key' => '',
        'secret' => '',
    ],

    'facebook' => [
        'client_id' => env('FB_CLIENT_ID'),
        'client_secret' => env('FB_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/connect/facebook/callback',
    ],

    'twitter' => [
        'client_id' => env('TW_CLIENT_ID'),
        'client_secret' => env('TW_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/connect/twitter/callback',
    ],

    'linkedin' => [
        'client_id' => env('LI_CLIENT_ID'),
        'client_secret' => env('LI_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/connect/twitter/callback',
    ],

];
