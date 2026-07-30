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
    | Shared secret sent by Telegram in the X-Telegram-Bot-Api-Secret-Token
    | header on every webhook request (set via setWebhook). The webhook
    | endpoint rejects anything that does not match. Used only in non-local
    | environments, where updates arrive by webhook instead of polling.
    */
    'webhook_secret' => env('TELEGRAM_COFFEE_BOT_WEBHOOK_SECRET', ''),

    /*
    | Base URL used to build per-meeting video call links.
    */
    'jitsi_base_url' => env('COFFEE_JITSI_BASE_URL', 'https://meet.jit.si'),

    /*
    | Lifetime of the signed links the bot sends: the "did it happen?" yes/no
    | answer and the feedback form opened behind the "yes" one.
    */
    'link_ttl_days' => 7,

    /*
    | Short feedback survey shown right after a participant confirms that the
    | meeting took place. The questions live here rather than in the database:
    | the list changes rarely, and every stored answer keeps a snapshot of the
    | question text, so re-wording a question never rewrites past answers.
    |
    | type: bool   - Так / Ні
    |       choice - own options, value => label
    |       text   - free-form textarea
    | ":duration" inside a label is replaced with the configured meeting length,
    | so the question cannot go stale when the admin changes it in settings.
    */
    'survey' => [

        'title' => 'Дякуємо, що знайшли час на Random Coffee! ☕',

        'intro' => 'Нам дуже цікаво дізнатися, як пройшла ваша зустріч. Будемо вдячні, якщо '
            . 'відповісте на кілька коротких запитань — ваш фідбек допоможе зробити наступні '
            . 'Random Coffee ще більш цікавими та корисними.',

        'questions' => [
            [
                'key' => 'useful',
                'type' => 'bool',
                'label' => 'Чи була ця зустріч для вас корисною?',
                'required' => true,
            ],
            [
                'key' => 'again',
                'type' => 'choice',
                'label' => 'Чи хотіли б ви зустрітися з іншим випадковим колегою ще раз через певний період?',
                'options' => [
                    'yes' => 'Так',
                    'no' => 'Ні',
                    'maybe' => 'Можливо',
                ],
                'required' => true,
            ],
            [
                'key' => 'duration_enough',
                'type' => 'bool',
                'label' => 'Чи достатньо :duration хвилин для такої зустрічі?',
                'required' => true,
            ],
            [
                'key' => 'suggestions',
                'type' => 'text',
                'label' => 'Ваші побажання або пропозиції (за бажанням)',
                'required' => false,
            ],
        ],

    ],

];