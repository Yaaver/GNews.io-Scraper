<?php

namespace YK\CustomPlugin\actions;

use YK\CustomPlugin\Hook;
class FetchACFCategoriesList extends Hook
{
    public static array $hooks = [
        'wp_ajax_fetch_acf_categories',
    ];

    public function __invoke(): void
    {
        $this->handle_fetch_acf_categories();
    }

    function handle_fetch_acf_categories() {
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'customplugin_nonce')) {
            wp_send_json_error(array(
                'message' => 'Invalid nonce'
            ));
        }

        // Get ACF categories
        $categories = get_terms(array(
            'taxonomy' => 'news-category',
            'hide_empty' => false, // Include categories with no posts
        ));

        if (is_wp_error($categories)) {
            wp_send_json_error(array(
                'success' => false,
                'message' => 'Error fetching categories'
            ));
            return;
        }

        $category_data = array();
        foreach ($categories as $category) {
            $category_data[] = array(
                'slug' => $category->slug,
                'name' => $category->name
            );
        }

        wp_send_json_success(array(
            'success' => true,
            'categories' => $category_data
        ));
    }
}