<?php

/**
 * Proxy a URL and return the response as an array.
 *
 * @param $url
 * @return array
 */
function proxy ($url) {

    // init response array
    $response = array();

    // init curl & set options
    $ch = curl_init();
    $curl_options = array (
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_COOKIE => getClientCookies(),

        // required for cookies bridging
        CURLOPT_VERBOSE => true,
        CURLOPT_HEADER => true,
    );
    curl_setopt_array($ch, $curl_options);

    // catch a POST request and forward it along with query
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST, '', '&'));
    }

    // stop session so curl can use the same session without conflicts
    session_write_close();

    // execute curl request
    $output = curl_exec($ch);

    /*****************************/
    /* This happens after the request has been made */

    // get response content-type and send to the client
    $response['content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    // get effective (final) URL to determine if redirection is needed
    $response['eff_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    // Separate header and body
    $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($output, 0, $header_len);
    $response['body'] = substr($output, $header_len);

    curl_close($ch);

    // restart session
    session_start();

    // set cookies to client using a separate function
    setClientCookies($header, $url);

    // return the response to the client
    return $response;
}

/**
 * Get client cookies and return a string to send using cURL
 *
 * @return string
 */
function getClientCookies () {
    $cookies = array();

    foreach ($_COOKIE as $key => $value)
    {
        // PHP can't use dots in cookie names, and these should be altered
        if ($key === 'ASP_NET_SessionId') $key = 'ASP.NET_SessionId';
        if ($key === 'proxy') continue;

        if ($key != 'Array') {
            $cookies[] = $key . '=' . $value;
        }
    }

    // ezproxy user cookie
    array_push($cookies, 'ezproxy=' . trim(explode('ezproxy', file_get_contents('ezlogin'))[1]));

    return implode(';', $cookies);
}

/**
 * Set client cookies from cURL response header
 * and strip domain part to set all cookies to the proxy host
 *
 * @param $header
 * @param $url
 */
function setClientCookies ($header, $url) {

    $cookies = array();

    // insert all cookies to an array
    preg_match_all('/^(Set-Cookie:\s*[^\n]*)$/mi', $header, $cookies);

    // loop the array
    foreach($cookies[0] as $cookie) {

        // strip domain part from cookie to set it to the proxy host
        $cookie_parts = explode(' Domain=', $cookie);

        // send cookie to the client
        header($cookie_parts[0], false);
    }
}

/**
 * Fast & easy proxifying, it handles redirects and output to client.
 *
 * Example: just call proxify ('http', 'example.com')
 * and everything else will happen magically.
 *
 * @param $scheme
 * @param $host
 * @param $plugin. optional - specify a plugin name to handle site-specific requirements
 *                 the filename should be [plugin name].plugin.php
 *                 a function [plugin name]_init() must be present.
 * @param $plugin_filename. optional plugin filename instead of [plugin_name].plugin.php
 * @return mixed
 */
function proxify ($scheme, $host, $plugin = false, $plugin_filename = false) {

    // build URL
    $url = $scheme.'://'.$host.$_SERVER['REQUEST_URI'];

    // request the webpage and store the response
    $response = proxy($url);


    // effective URL = the actual URL after redirects
    $eff_url = parse_url($response['eff_url']);
    // effective request = request path & query (without the hostname)
    $eff_req = $eff_url['path'].(isset($eff_url['query']) ? '?'.$eff_url['query'] : '');

    if ($eff_url['host'] != $host) {

        // if an external redirect has been made, display a warning
        echo '<h2>The page redirects outside the proxified host.</h2><p>To na'
            .'vigate there, <a href="'.$response['eff_url'].'" target="_blank">click here</a>.</p>';
        die();
    } else if ($eff_req != $_SERVER['REQUEST_URI']) {

        // if an internal (within the proxified host) has been made, just go there
        echo proxy($response['eff_url'], true);
        header('Location: '.$eff_req);
    }

    if ($plugin) {

        // execute user plugin

        require_once ($plugin_filename ? $plugin_filename : $plugin . '.plugin.php');
        echo call_user_func($plugin . '_init', $response);
        die();
    } else {

        // default = set original content-type and echo the response.

        header('Content-Type: '. $response['content_type']);
        print $response['body'];
        die();
    }
}
