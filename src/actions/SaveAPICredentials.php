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
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_settings_page(): void
    {
        Template::render('admin/customplugin-settings-page');
    }

    public function register_settings()
    {
        register_setting( 'gnews_settings', 'gnews_api_key', array( $this, 'sanitize_api_key' ) );
        // Add additional settings for other credentials if needed
    }

    public function sanitize_api_key( $api_key ): string
    {
        $api_key = sanitize_text_field( $api_key );
        // You can perform additional validation here (e.g., check API key format)
        if (strlen($api_key) > 40) { // Basic length check
            add_settings_error( 'gnews_settings', 'error_message', 'API Key is too long. Please check the documentation for the correct format.', 'error' );
        } else {
            // Check for alphanumeric characters, underscores, hyphens
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $api_key)) {
                add_settings_error( 'gnews_settings', 'error_message', 'Invalid API Key format. Please use only alphanumeric characters, underscores, and hyphens.', 'error' );
                return $api_key; // Exit if format is invalid
            }

            // API key format seems valid, perform API validation
            $api_url = 'https://gnews.io/api/v4/search?'; // Base URL for top headlines
            $api_params = array(
                'q' => 'news',
                'max' => 5, // Limit the number of articles to 5
                'category' => 'general', // Filter by business category
                'lang' => 'en', // Set language to English
                'country' => 'pk', // Set country to Pakistan
                'apikey' => $api_key, // Add your API key here
            );

            $request_url = $api_url . http_build_query($api_params); // Build the request URL
            echo "Request URL: $request_url<br>"; // Print the request URL
//            $request_url = 'https://gnews.io/api/v4/search?q=example&apikey=14deb43c6e6e63294fde00ec1025d156';

            $response = wp_remote_get($request_url); // Use WordPress function for remote GET request

            if (is_wp_error($response)) { // Check for errors during the request
                $error_message = $response->get_error_message();
                add_settings_error( 'gnews_settings', 'error_message', "Error connecting to GNews API: $error_message", 'error' );
                return $api_key;
            } else {
                $body = wp_remote_retrieve_body($response); // Get the response body
                $data = json_decode($body, true); // Decode the JSON data

                // Loop through the "articles" array and access the "title" property
                if (isset($data['articles'])) {
                    foreach ($data['articles'] as $article) {
                        echo $article['title'] . "<br>"; // Print each title with a newline
                    }
                } else {
                    echo "No articles found in the data."; // Handle the case where there are no articles
                }

                echo "<pre>";
print_r($data);
echo "</pre>";
die();
                if (isset($data['status']) && $data['status'] === 'ok') { // Check for successful response
                    add_settings_error( 'gnews_settings', 'success_message', 'API Key saved successfully! Validated with GNews API.', 'success' );
                    // You can now access the fetched data from $data variable for further processing
                } else {
                    $error_message = '';
                    // Attempt to extract error message from the response (if available)
                    if (isset($data['message'])) {
                        $error_message = $data['message'];
                    } elseif (isset($data['error'])) {
                        $error_message = $data['error']; // Check for alternative error property
                    }

                    if (empty($error_message)) {
                        $error_message = 'Unknown error from GNews API.';
                    }
                    add_settings_error( 'gnews_settings', 'error_message', "API Key validation failed: $error_message", 'error' );
                }
            }
        }

        return $api_key;
    }
}