<?php

namespace YK\CustomPlugin\admin;

use YK\CustomPlugin\Hook;
use YK\CustomPlugin\Template;

class AdminSettingsPage extends Hook
{
    public static array $hooks = ['admin_menu'];

    public function __invoke(): void
    {
        add_submenu_page(
            'yk-customplugin-page',
            'Settings - Custom Plugin',
            'Settings',
            'manage_options',
            'cp_settings_page',
            array($this, 'render_submenu_page'),
        );
    }

    public function render_submenu_page(): void
    {
        Template::render('admin/cp-settings-page');
    }
}
