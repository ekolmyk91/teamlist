<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Random Coffee
    |--------------------------------------------------------------------------
    |
    | Runtime settings for the Random Coffee module. Business rules that the
    | admin may change (frequency, matching day, meeting time, etc.) live in
    | the coffee_settings table; this file only holds infrastructure values.
    |
    */

    'timezone' => env('COFFEE_TIMEZONE', 'Europe/Kyiv'),

    /*
    | Dedicated Telegram bot for personal Random Coffee messages. This is a
    | separate bot from the events one: employees link themselves by sending
    | their work email to it, and receive only their own meetings.
    */
    'bot_token' => env('TELEGRAM_COFFEE_BOT_TOKEN', ''),
    'bot_username' => env('TELEGRAM_COFFEE_BOT_USERNAME', ''),

    /*
    | Base URL used to build per-meeting video call links.
    */
    'jitsi_base_url' => env('COFFEE_JITSI_BASE_URL', 'https://meet.jit.si'),

];