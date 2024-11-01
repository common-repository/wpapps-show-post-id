<?php
/**
 * Plugin Name: WPApps Show Post ID
 * Plugin URI: https://wpapps.net
 * Description: This plugin shows the Post ID in the Post and Pages List in the Dashboard for both Posts and Pages.
 * Version: 2.0.0
 * Author: WPApps
 * Author URI: https://wpapps.net
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: show-post-id
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add a new column for Post ID in the Posts and Pages List
 *
 * @param array $columns - Array of existing columns
 * @return array - Updated array of columns
 */
function show_post_id_column($columns)
{
    $columns['post_id'] = __('Post ID', 'show-post-id');
    return $columns;
}

/**
 * Display the Post ID in the new column for Posts and Pages List
 *
 * @param string $column_name - Current column name
 * @param int $post_id - Current post ID
 */
function show_post_id_column_content($column_name, $post_id)
{
    if ($column_name === 'post_id' && !did_action("show_post_id_{$post_id}")) {
        echo esc_html($post_id);
        do_action("show_post_id_{$post_id}"); // Set an action to ensure we don't run this multiple times for the same post ID
    }
    return; 
}

//add JS script
function enqueue_show_post_id_scripts($hook) {
    // Check if we're on the edit posts or pages screen
    if ('edit.php' === $hook) {
        // Enqueue the script with jQuery as a dependency
        wp_enqueue_script('show-post-id-script', plugin_dir_url(__FILE__) . 'js/show-post-id.js', array('jquery'), '1.0.0', true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_show_post_id_scripts');

// Function to print inline JavaScript in the admin footer
function display_post_id_next_to_new_title_js() {
    global $post;

    // Only run this on post editing screens
    if (isset($post) && $post->ID && (get_current_screen()->base == 'post')) {
        echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                // Target the new Edit Page title structure and append the Post ID
                $("h1.wp-heading-inline").append(" (ID: ' . $post->ID . ')");
            });
        </script>';
    }
}

// Hook the function to the 'admin_footer' action
add_action('admin_footer', 'display_post_id_next_to_new_title_js');

// Function to add the Post ID to the admin bar
function add_post_id_to_admin_bar($wp_admin_bar) {
    // Check if we're on the frontend, viewing a single post or page, and the current user can edit posts
    if (!is_admin() && is_singular() && current_user_can('edit_posts')) {
        global $post;
        $wp_admin_bar->add_node(array(
            'id' => 'display-post-id',
            'title' => 'Post ID: ' . $post->ID,
            'href' => get_edit_post_link($post->ID)
        ));
    }
}

// Hook the function to the 'admin_bar_menu' action with a priority of 100 to ensure it's added at the end
add_action('admin_bar_menu', 'add_post_id_to_admin_bar', 100);



/**
 * Make the Post ID column sortable
 *
 * @param array $columns - Array of sortable columns
 * @return array - Updated array of sortable columns
 */
function show_post_id_sortable_column($columns)
{
    $columns['post_id'] = 'ID';
    return $columns;
}

// Hooks for posts and pages
add_filter('manage_posts_columns', 'show_post_id_column');
add_filter('manage_pages_columns', 'show_post_id_column');
add_action('manage_posts_custom_column', 'show_post_id_column_content', 10, 2);
add_action('manage_pages_custom_column', 'show_post_id_column_content', 10, 2);
add_filter('manage_edit-post_sortable_columns', 'show_post_id_sortable_column');
add_filter('manage_edit-page_sortable_columns', 'show_post_id_sortable_column');

// Add support for custom post types
function add_support_for_custom_post_types() {
    $post_types = get_post_types(array('public' => true, '_builtin' => false), 'names', 'and');
    
    foreach ($post_types as $post_type) {
        add_filter("manage_{$post_type}_posts_columns", 'show_post_id_column');
        add_action("manage_{$post_type}_posts_custom_column", 'show_post_id_column_content', 9999, 2); // Adjusted priority
        add_filter("manage_edit-{$post_type}_sortable_columns", 'show_post_id_sortable_column');
    }
}
add_action('admin_init', 'add_support_for_custom_post_types');

// Function to add a new bulk action option dynamically to all post types
function add_copy_post_ids_bulk_action($bulk_actions) {
  $bulk_actions['copy_post_ids'] = __('Copy Post IDs', 'show-post-id');
  return $bulk_actions;
}

// Function to hook our bulk action addition to all post types
function hook_copy_post_ids_bulk_action_to_all_post_types() {
    $post_types = get_post_types(array('public' => true, '_builtin' => false), 'names', 'and');
    foreach ($post_types as $post_type) {
        add_filter("bulk_actions-edit-{$post_type}", 'add_copy_post_ids_bulk_action');
    }
}

// Add the new bulk action to built-in post and page post types
add_filter('bulk_actions-edit-post', 'add_copy_post_ids_bulk_action');
add_filter('bulk_actions-edit-page', 'add_copy_post_ids_bulk_action');

// Use the 'init' action to ensure all post types are initialized before adding our bulk action
add_action('init', 'hook_copy_post_ids_bulk_action_to_all_post_types', 20);

?>