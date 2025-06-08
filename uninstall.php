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
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    
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
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'formshive_%'");
    
    // Remove transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_formshive_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_formshive_%'");
    
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
    
    // Log uninstall for debugging (if debug mode is enabled)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Formshive plugin has been uninstalled and all data removed.');
    }
}

/**
 * Recursively remove directory and its contents
 *
 * @param string $dir Directory path to remove
 */
function formshive_remove_directory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    
    foreach ($files as $file) {
        $file_path = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($file_path)) {
            formshive_remove_directory($file_path);
        } else {
            unlink($file_path);
        }
    }
    
    rmdir($dir);
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