<?php

/**
 * Encode JSON for use in HTML.
 *
 * @param array|\Illuminate\Contracts\Support\Jsonable $data
 * @param int                                          $options
 *
 * @return string
 */
function json_encode_html(array|Illuminate\Contracts\Support\Jsonable $data, int $options = 0): string
{
    $json = $data instanceof Illuminate\Contracts\Support\Jsonable
        ? $data->toJson($options)
        : json_encode($data, $options);

    return htmlspecialchars($json, ENT_QUOTES);
}

/**
 * Get the user agent for the application.
 *
 * @return string
 */
function user_agent(): string
{
    return str_replace(' ', '', config('app.name')) . '/' . config('app.version') . ' (+' . route('home') . ')';
}

/**
 * Get an HTTP client to use with sane timeouts and defaults.
 *
 * @param string|null   $baseUri
 * @param array         $headers
 * @param int           $timeout
 * @param array         $options
 * @param \Closure|null $stackCallback
 *
 * @return \GuzzleHttp\Client
 */
function http(?string $baseUri = null, array $headers = [], int $timeout = 10, array $options = [], ?Closure $stackCallback = null): GuzzleHttp\Client
{
    $stack = GuzzleHttp\HandlerStack::create();

    if (app()->bound('sentry')) {
        $stack->push(Sentry\Tracing\GuzzleTracingMiddleware::trace());
    }

    if ($stackCallback !== null) {
        $stackCallback($stack);
    }

    return new GuzzleHttp\Client(array_merge($options, [
        'base_uri'        => $baseUri,
        'handler'         => $stack,
        'timeout'         => $timeout,
        'connect_timeout' => $timeout,
        'headers'         => array_merge($headers, [
            'User-Agent' => user_agent(),
        ]),
    ]));
}
