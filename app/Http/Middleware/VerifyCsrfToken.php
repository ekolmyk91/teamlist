<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Random Coffee Telegram webhook: an external POST from Telegram that
        // carries no CSRF token; authenticated by its secret-token header.
        'coffee/telegram/webhook',
        // Random Coffee feedback survey: a public page opened from a signed
        // bot link, possibly days later, so it cannot rely on a session token;
        // the URL signature authenticates the request instead.
        'coffee/survey/*',
    ];
}
