<?php

namespace YK\CustomPlugin\admin;

use YK\CustomPlugin\Hook;
use YK\CustomPlugin\Template;

class AdminMenu extends Hook
{
    public static array $hooks = ['admin_menu'];

    public function __invoke(): void
    {
        $this->register_menu_page();
    }

    public function register_menu_page(): void
    {
        add_menu_page(
            'Custom Plugin',
            'CustomPlugin',
            'manage_options',
            'yk-customplugin-page',
            array($this, 'render_menu_page'),
            'dashicons-admin-generic',
            2
        );
    }

    public function render_menu_page(): void
    {
        Template::render('admin/cp-main');
    }
}