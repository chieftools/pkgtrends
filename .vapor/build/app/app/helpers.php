<?php

/**
 * Encode JSON for use in HTML.
 *
 * @param \Illuminate\Contracts\Support\Jsonable|array $data
 * @param int                                          $options
 *
 * @return string
 */
function json_encode_html($data, $options = 0)
{
    $json = ($data instanceof \Illuminate\Contracts\Support\Jsonable) ?
        $data->toJson($options) : json_encode($data, $options);

    return htmlspecialchars($json, ENT_QUOTES);
}
