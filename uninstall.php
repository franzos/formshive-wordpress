<?php
/**
 * Uninstall Formshive Plugin
 *
 * This file is executed when the plugin is uninstalled via WordPress admin.
 * It removes all plugin data including database tables, options, and files.
 *
 * @package Formshive
 * @since 0.1.0
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Security check - only run if we're actually uninstalling this plugin
if (!defined('FORMSHIVE_PLUGIN_FILE')) {
    define('FORMSHIVE_PLUGIN_FILE', __FILE__);
}

/**
 * Remove all plugin data
 */
function formshive_uninstall_plugin() {
    global $wpdb;
    
    // Remove database tables
    $table_name = $wpdb->prefix . 'formshive_forms';
    // Sanitize table name and use direct query (acceptable in uninstall context)
    $table_name = esc_sql($table_name);
    $wpdb->query("DROP TABLE IF EXISTS `{$table_name}`");
    
    // Remove plugin options
    $options = array(
        'formshive_settings',
        'formshive_api_endpoint',
        'formshive_default_framework',
        'formshive_version',
        'formshive_db_version'
    );
    
    foreach ($options as $option) {
        delete_option($option);
        delete_site_option($option); // For multisite
    }

    // Remove user meta data related to the plugin
    // Use WordPress function to get all users and remove meta
    $users = get_users(array('fields' => 'ID'));
    foreach ($users as $user_id) {
        $meta_keys = get_user_meta($user_id);
        foreach ($meta_keys as $key => $value) {
            if (strpos($key, 'formshive_') === 0) {
                delete_user_meta($user_id, $key);
            }
        }
    }

    // Remove transients using WordPress functions
    formshive_delete_all_transients();
    
    // Remove any uploaded files (if they exist)
    $upload_dir = wp_upload_dir();
    $formshive_upload_dir = $upload_dir['basedir'] . '/formshive';
    
    if (is_dir($formshive_upload_dir)) {
        formshive_remove_directory($formshive_upload_dir);
    }
    
    // Clear any cached data
    wp_cache_flush();
    
    // Remove capabilities (if any were added)
    $role = get_role('administrator');
    if ($role) {
        $role->remove_cap('manage_formshive_forms');
        $role->remove_cap('edit_formshive_forms');
        $role->remove_cap('delete_formshive_forms');
    }
}

/**
 * Delete all formshive transients using WordPress functions
 */
function formshive_delete_all_transients()
{
    // Get all options and filter for transients
    $all_options = wp_load_alloptions();

    foreach ($all_options as $option_name => $option_value) {
        // Check for formshive transients
        if (strpos($option_name, '_transient_formshive_') === 0) {
            // Extract transient name (remove _transient_ prefix)
            $transient_name = substr($option_name, 11); // Remove '_transient_' (11 chars)
            delete_transient($transient_name);
        } elseif (strpos($option_name, '_transient_timeout_formshive_') === 0) {
            // Extract transient name (remove _transient_timeout_ prefix)  
            $transient_name = substr($option_name, 19); // Remove '_transient_timeout_' (19 chars)
            delete_transient($transient_name);
        }
    }
}

/**
 * Recursively remove directory and its contents using WP_Filesystem
 *
 * @param string $dir Directory path to remove
 */
function formshive_remove_directory($dir) {
    if (!is_dir($dir)) {
        return;
    }

    // Initialize WP_Filesystem
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();
    }

    // Use WP_Filesystem to remove directory recursively
    if ($wp_filesystem && $wp_filesystem->exists($dir)) {
        $wp_filesystem->rmdir($dir, true); // true for recursive removal
    } else {
        // If WP_Filesystem not available, use wp_delete_file for files
        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $file_path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($file_path)) {
                formshive_remove_directory($file_path);
            } else {
                // Always use wp_delete_file()
                wp_delete_file($file_path);
            }
        }

        // For directory removal, we need to use WP_Filesystem
        // Re-attempt with WP_Filesystem for the empty directory
        if ($wp_filesystem) {
            $wp_filesystem->rmdir($dir);
        }
    }
}

/**
 * Ask user for confirmation before removing data
 * This function creates an admin notice for data removal confirmation
 */
function formshive_uninstall_confirmation() {
    // This runs only during plugin deactivation, not uninstall
    // The actual uninstall happens when user confirms in WordPress admin
    
    // Add admin notice for data removal option
    add_option('formshive_pending_uninstall', true);
}

// Run the uninstall process
formshive_uninstall_plugin();