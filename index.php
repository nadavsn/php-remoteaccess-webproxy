<?php
/**
 * Everything starts here!
 */

// Set E_ALL for debugging, as always...
error_reporting(E_ALL);

// Authenticate the user first.
require_once 'auth.php';

// Core
require_once 'proxy.php';

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
 */
proxify('http', 'example.com');
