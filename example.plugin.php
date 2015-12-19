<?php

/**
 * Init function - REQUIRED.
 * This is the function that will be called
 * right after the proxifying.
 *
 * @param $response - get the proxy response
 * @return string - return the response
 */
function example_init($response) {

    // set original content type
    header('Content-Type: '. $response['content_type']);

    /**
     * Do here whatever you want to manipulate the response
     * before you send it to the end user
     */

    return $response['body'];

};
