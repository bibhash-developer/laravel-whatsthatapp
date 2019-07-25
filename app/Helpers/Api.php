<?php

/*
 * Developer: Bibhash Shishodiya
 * Date: 28-May-2019
 */

// return error json
function api_create_response($payload, $status, $message) {
    if (is_numeric($payload)) {

        if ($payload == 1)
            $payload = array();
        elseif ($payload == 2)
            $payload = new \stdClass();
    }

    $response_array = array('payload' => $payload, 'status' => $status, 'message' => $message);
    return $response_array;
}

function unsetKeys($array, $unsetKey) {
    foreach($unsetKey as $u) {
        unset($array[$u]);
    }
    return $array;
}