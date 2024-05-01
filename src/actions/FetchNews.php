<?php

namespace YK\CustomPlugin\actions;

use YK\CustomPlugin\Hook;

class FetchNews extends Hook
{
    public static array $hooks = [
        'wp_ajax_yk_fetch_news',
    ];

    public function __invoke(): void
    {
        $this->handle_fetch_news();
    }

    public function handle_fetch_news(): void
    {
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'customplugin_nonce')) {
            wp_send_json_error(array(
                'message' => 'Invalid nonce'
            ));
            return;
        }

        $api_key = get_option('gnews_api_key');
        $formData = $_POST['news_data'];
        if (!is_array($formData)) {
            wp_send_json_error(array(
                'message' => 'Error: Invalid data received.'
            ));
        }

        // Build API URL based on form data
        $api_url = 'https://gnews.io/api/v4/search?';
        $api_params = array(
            'q' => 'news',
            'max' => 5, // Limit the number of articles to 5
            'category' => 'general', // Filter by business category
            'lang' => 'en', // Set language to English
            'country' => 'pk', // Set country to Pakistan
            'apikey' => $api_key, // Add your API key here
        );

        // Assign each API parameter from the form data
        foreach ($formData as $item) {
            $api_params[$item['name']] = $item['value'];
        }

        $request_url = $api_url . http_build_query($api_params);

        $response = wp_remote_get($request_url);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            wp_send_json_error(array(
                'message' => $error_message
            ));
        }

        $articles = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

        $created_posts = 0;
        $ignored_duplicates = 0;
        $duplicate_titles = [];
        foreach ($articles['articles'] as $article_data) {

            $title = wp_strip_all_tags($article_data['title']);

            $existing_post = get_posts(array(
                'post_type' => 'news', // Change to your CPT slug
                'name' => $title, // Match title exactly
                'numberposts' => 1, // Limit to 1 result
            ));

            // Prepend a unique identifier if a post with the same title exists
            if ($existing_post) {
                $ignored_duplicates++;
                $duplicate_titles[] = $title; // Add duplicate title to the array
                continue;
            }

            // Proceed with normal post creation
            $post_data = [
                'post_type' => 'news',
                'post_title' => wp_strip_all_tags($article_data['title']), // Sanitize title
                'post_content' => wp_kses_post($article_data['content']), // Sanitize content
                'post_status' => 'publish', // Change to 'draft' for drafts
            ];

            // Add meta fields (replace with your actual field names)
            $post_data['meta_input'] = [
                '_news_source' => $article_data['source']['name'],
                '_news_source_url' => $article_data['source']['url'],
                '_featured_image' => $article_data['image'], // URL of featured image
            ];

            $post_id = wp_insert_post($post_data);

            // Download and attach featured image
            $featured_image_url = $article_data['image']; // Get URL from meta data

            if (!is_wp_error($post_id)) {
                // Set featured image from URL
                $this->set_featured_image_from_url($post_id, $featured_image_url);

                // Check if category is present in API parameters
                $category_name = 'general'; // Define a default category name
                if (isset($api_params['category'])) {
                    // Assign the category ID using wp_set_post_categories
                    $post_data['post_category'] = array($api_params['category']);
                    $category_name = $api_params['category'];
                }
                // Assign category to ACF taxonomy field
                $tax = 'news-category'; // Taxonomy slug
                $this->assign_category_name_to_taxonomy($post_id, $category_name, $tax);


                // Check if source and source URL is present in API parameters
                if (isset($article_data['source']['name']) && isset($article_data['url'])) {
                    update_field('source', $article_data['source']['name'], $post_id); // Update source ACF field in post
                    update_field('source_url', array('url' => $article_data['url']), $post_id); // Update source URL ACF field in post
                }

                // Assign published_date to acf field 'published_date'
                $published_date = $article_data['publishedAt'];
                if (!empty($published_date) && strtotime($published_date)) {
                    $published_date = date('Y-m-d H:i:s', strtotime($published_date));
                } else {
                    $published_date = current_time('mysql');
                }
                update_field('published_date', $published_date, $post_id); // Update published date ACF field in post

                $created_posts++;
            }
        }

        if ($ignored_duplicates > 0) {
            // Display duplicate titles
            $duplicate_counter = 0;
            $duplicate_titles_list = "<h3>Skipped Articles (Duplicate Titles):</h3>";
            $duplicate_titles_list .= "<ul>";
            foreach ($duplicate_titles as $duplicate_title) {
                $duplicate_counter++;
                $duplicate_titles_list .= "<li>$duplicate_counter- $duplicate_title</li>";
            }
            $duplicate_titles_list .= "</ul>";
        }
        $message = sprintf('%d news articles created successfully, %d duplicates ignored. %s', $created_posts, $ignored_duplicates, $duplicate_titles_list);
        wp_send_json_success(array(
            'message' => $message
        ));
    }

    // Fetch image from URL and set as featured image
    private function set_featured_image_from_url($post_id, $image_url) {
        // Fetch image content from URL
        $image_data = file_get_contents($image_url);

        // Check if image content was fetched successfully
        if ($image_data) {
            // Create unique filename for the image
            $filename = md5($image_url) . '.jpg'; // You can use any desired file extension

            // Path to upload directory
            $upload_dir = wp_upload_dir();

            // Path to save the image locally
            $image_path = $upload_dir['path'] . '/' . $filename;

            // Save the image locally
            file_put_contents($image_path, $image_data);

            // Set up the image data for attachment
            $attachment = array(
                'post_mime_type' => 'image/jpeg', // Adjust mime type based on file type
                'post_title'     => sanitize_file_name($filename),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            // Insert the image as attachment
            $attach_id = wp_insert_attachment($attachment, $image_path, $post_id);

            // Set the image as featured image for the post
            if (!is_wp_error($attach_id)) {
                set_post_thumbnail($post_id, $attach_id);

                return $attach_id; // Return the attachment ID
            } else {
                return false; // Return false if attachment insertion failed
            }
        } else {
            return false; // Return false if image content couldn't be fetched
        }
    }

    // Assign category to the post and also to ACF post taxonomy field
    private function assign_category_name_to_taxonomy($post_id, $category_name, $tax): void
    {
        $term = get_term_by('name', $category_name, $tax);

        // Check if term exists
        if ($term && !is_wp_error($term)) {
            // Get term ID
            $term_id = $term->term_id;

            // Update ACF taxonomy field value for post
            update_field('category', $term_id, $post_id); // Update the field name to 'category'

            // Assign category to the post
            wp_set_post_terms($post_id, array($term_id), $tax, false); // Pass the term ID as an array

        }
    }
}