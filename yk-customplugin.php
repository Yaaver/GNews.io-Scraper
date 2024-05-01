<?php

/**
 * Plugin Name:     Custom Plugin
 * Description:     Custom Plugin.
 * Author:          Yawar Abbas Khokhar
 * Version:         1.0
 * Text Domain:     yk-customplugin
 */


if (!defined('ABSPATH')) {
    exit;
}


const YK_CustomPlugin_VERSION = '1.0.0';
define('YK_CustomPlugin_DIR', plugin_dir_path(__FILE__));
define('YK_CustomPlugin_URL', plugin_dir_url(__FILE__));

require 'vendor/autoload.php';

\YK\CustomPlugin\App::get_instance();