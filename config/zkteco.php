<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZKTeco Sync API Key
    |--------------------------------------------------------------------------
    | This key is used to authenticate the local agent pushing punch data
    | to the live server. Must match the key in local agent .env file.
    */
    'sync_api_key' => env('ZKTECO_SYNC_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Device IDs
    |--------------------------------------------------------------------------
    | Whitelist of device IDs allowed to push data.
    | Leave empty to allow all devices.
    */
    'allowed_devices' => array_filter(
        explode(',', env('ZKTECO_ALLOWED_DEVICES', ''))
    ),
];