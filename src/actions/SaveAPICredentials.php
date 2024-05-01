<?php

namespace YK\CustomPlugin\actions;

use YK\CustomPlugin\Hook;
use YK\CustomPlugin\Template;

class SaveAPICredentials extends Hook
{
    public static array $hooks = [
        'admin_menu',
        'admin_init'
    ];

    public function __invoke(): void
    {
        $this->setup_settings(); // Call the new method
    }

    private function setup_settings(): void
    {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_settings_page(): void
    {
        Template::render('admin/customplugin-settings-page');
    }

    public function register_settings(): void
    {
        register_setting('gnews_settings', 'gnews_api_key', array($this, 'sanitize_api_key'));
    }

    public function sanitize_api_key($api_key): string
    {
        $api_key = sanitize_text_field($api_key);
        // You can perform additional validation here (e.g., check API key format)
        if (strlen($api_key) > 40) { // Basic length check
            add_settings_error('gnews_settings', 'error_message', 'API Key is too long. Please check the documentation for the correct format.', 'error');
        }
        else {
            add_settings_error('gnews_settings', 'error_message', 'API Key updated successfully.', 'updated');
            return $api_key;
        }
        return '';
    }
}