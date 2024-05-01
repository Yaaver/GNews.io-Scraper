<?php

namespace YK\CustomPlugin;

class Template
{
    /**
     * Retreives a template
     *
     * @param string Route, @param string Props
     * @return string
     */
    public static function get($route, $props = [])
    {
        ob_start();

        self::render($route, $props);

        return ob_get_clean();
    }

    /**
     * Renders a template
     *
     * @param string Route, @param array Props
     * @return void
     */
    public static function render($route, $props = [])
    {
        extract($props);

        include YK_CustomPlugin_DIR . "templates/{$route}.html.php";
    }
}
