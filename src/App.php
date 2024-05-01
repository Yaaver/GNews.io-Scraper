<?php

namespace YK\CustomPlugin;

use YK\CustomPlugin\admin\AdminMenu;
use YK\CustomPlugin\admin\AdminSettingsPage;

use YK\CustomPlugin\actions\SaveAPICredentials;
use YK\CustomPlugin\actions\FetchNews;
use YK\CustomPlugin\actions\FetchACFCategoriesList;

class App {
    /**
     * Instance of app
     *
     * @var App
     */
    protected static $instance;

    /**
     * List of actions and their handlers
     *
     * @var array
     */
    protected array $actions = array(
        AdminMenu::class,
        AdminSettingsPage::class,

        SaveAPICredentials::class,
        FetchNews::class,
        FetchACFCategoriesList::class,

    );

    /**
     * List of filters and their handlers
     *
     * @var array
     */
    protected array $filters = array(
    );

    /**
     * List of shortcodes and their handlers
     *
     * @var array
     */
    protected array $shortcodes = array(
//        'shortcode_name' => ShortCodeClass::class,
    );

    /**
     * Construct app
     */
    public function __construct()
    {
//		$this->create_tables();
        $this->setup_hooks();
        $this->setup_shortcodes();

        add_action('admin_enqueue_scripts', array($this, 'setup_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'setup_frontend_assets'));
    }

    /**
     * Set up hooks
     */
    public function setup_hooks(): void
    {
        foreach ($this->actions as $handler) {
            foreach ($handler::$hooks as $action) {
                add_action($action, new $handler, $handler::$priority, $handler::$arguments);
            }
        }

        foreach ($this->filters as $handler) {
            foreach ($handler::$hooks as $filter) {
                add_filter($filter, new $handler, $handler::$priority, $handler::$arguments);
            }
        }
    }

    /**
     * Set up shortcodes
     */
    public function setup_shortcodes(): void
    {
        foreach ($this->shortcodes as $shortcode => $handler) {
            add_shortcode($shortcode, new $handler);
        }
    }

    /**
     * Get current instance of app
     *
     * @return App
     */
    public static function get_instance(): App
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create all required tables
     */
    public function create_tables(): void
    {
        global $wpdb;
    }

    /**
     * Enqueue admin assets
     */
    public function setup_admin_assets(): void
    {
        if (strpos($_SERVER['REQUEST_URI'], 'page=yk-customplugin-page') !== false) {

            wp_enqueue_style('customplugin-main', YK_CustomPlugin_URL . 'assets/css/customplugin-main.css?h=' . uniqid('', true), [], YK_CustomPlugin_VERSION);

            wp_enqueue_script('customplugin-main-script', YK_CustomPlugin_URL . 'assets/js/customplugin-main.js?h=' . uniqid('', true), array('jquery'), YK_CustomPlugin_VERSION, true);

            wp_localize_script('customplugin-main-script', 'customplugin_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'customplugin_nonce' => wp_create_nonce('customplugin_nonce')
            ));
        }
    }

    /**
     * Set up assets for frontend
     */
    public function setup_frontend_assets(): void {
    }
}