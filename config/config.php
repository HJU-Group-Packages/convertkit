<?php

return [
    /**
     * Quickly disable ConvertKit
     */
    'enabled'     => env('CONVERTKIT_ENABLED', true),

    /**
     * In your account settings
     * https://app.convertkit.com/account/edit
     */
    'api_key'     => env('CONVERTKIT_API_KEY'),
    /**
     * In your account settings
     * https://app.convertkit.com/account/edit
     */
    'api_secret'  => env('CONVERTKIT_API_SECRET'),
    /**
     * Subscriber form
     * You can get it from the URL of form when you edit it
     * Example: https://app.convertkit.com/forms/designers/{FORM ID}/edit
     */
    'form_id'     => env('CONVERTKIT_FORM_ID'),
    /**
     * This is only here in case they change the URL at some point
     */
    'base_url'    => env('CONVERTKIT_BASE_URL', 'https://api.convertkit.com/v3/'),
];