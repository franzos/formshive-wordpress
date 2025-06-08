<?php
/**
 * Admin functionality for Formshive plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Formshive_Admin {
    
    private static $instance = null;
    private $db;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->db = Formshive_Database::get_instance();
        $this->init();
    }
    
    private function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_formshive_save_form', array($this, 'ajax_save_form'));
        add_action('wp_ajax_formshive_delete_form', array($this, 'ajax_delete_form'));
        add_action('wp_ajax_formshive_get_form', array($this, 'ajax_get_form'));
        add_action('wp_ajax_formshive_duplicate_form', array($this, 'ajax_duplicate_form'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Formshive Forms', 'formshive'),
            __('Formshive', 'formshive'),
            'manage_options',
            'formshive',
            array($this, 'admin_page'),
            'dashicons-feedback',
            30
        );
        
        add_submenu_page(
            'formshive',
            __('All Forms', 'formshive'),
            __('All Forms', 'formshive'),
            'manage_options',
            'formshive',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'formshive',
            __('Add New Form', 'formshive'),
            __('Add New', 'formshive'),
            'manage_options',
            'formshive-add-new',
            array($this, 'add_form_page')
        );
        
        add_submenu_page(
            'formshive',
            __('Settings', 'formshive'),
            __('Settings', 'formshive'),
            'manage_options',
            'formshive-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'formshive') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');
        
        wp_enqueue_script(
            'formshive-admin',
            FORMSHIVE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            FORMSHIVE_VERSION,
            true
        );
        
        wp_enqueue_style(
            'formshive-admin',
            FORMSHIVE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FORMSHIVE_VERSION
        );
        
        wp_localize_script('formshive-admin', 'formshive_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('formshive_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this form?', 'formshive'),
                'saving' => __('Saving...', 'formshive'),
                'saved' => __('Saved!', 'formshive'),
                'error' => __('Error occurred. Please try again.', 'formshive')
            )
        ));
    }
    
    /**
     * Main admin page - list all forms
     */
    public function admin_page() {
        $forms = $this->db->get_all_forms();
        include FORMSHIVE_PLUGIN_DIR . 'templates/admin/forms-list.php';
    }
    
    /**
     * Add new form page
     */
    public function add_form_page() {
        $form_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $form = $form_id ? $this->db->get_form($form_id) : null;
        
        include FORMSHIVE_PLUGIN_DIR . 'templates/admin/form-editor.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $settings = get_option('formshive_settings', array());
        include FORMSHIVE_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!wp_verify_nonce($_POST['formshive_settings_nonce'], 'formshive_settings')) {
            wp_die(__('Security check failed', 'formshive'));
        }
        
        $settings = array(
            'api_endpoint' => sanitize_url($_POST['api_endpoint']),
            'default_framework' => sanitize_text_field($_POST['default_framework']),
            'enable_dev_mode' => isset($_POST['enable_dev_mode'])
        );
        
        update_option('formshive_settings', $settings);
        
        add_settings_error(
            'formshive_settings',
            'formshive_settings_updated',
            __('Settings saved successfully!', 'formshive'),
            'updated'
        );
    }
    
    /**
     * AJAX: Save form
     */
    public function ajax_save_form() {
        check_ajax_referer('formshive_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'formshive'));
        }
        
        $form_id = intval($_POST['form_id']);
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'form_id' => sanitize_text_field($_POST['formshive_form_id']),
            'type' => sanitize_text_field($_POST['type']),
            'framework' => sanitize_text_field($_POST['framework']),
            'form_data' => $_POST['form_data'] ? json_decode(stripslashes($_POST['form_data']), true) : array()
        );
        
        if ($form_id) {
            $result = $this->db->update_form($form_id, $data);
        } else {
            $result = $this->db->insert_form($data);
            $form_id = $result;
        }
        
        if ($result !== false) {
            wp_send_json_success(array('form_id' => $form_id));
        } else {
            wp_send_json_error(__('Failed to save form', 'formshive'));
        }
    }
    
    /**
     * AJAX: Delete form
     */
    public function ajax_delete_form() {
        check_ajax_referer('formshive_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'formshive'));
        }
        
        $form_id = intval($_POST['form_id']);
        $result = $this->db->delete_form($form_id);
        
        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error(__('Failed to delete form', 'formshive'));
        }
    }
    
    /**
     * AJAX: Get form data
     */
    public function ajax_get_form() {
        check_ajax_referer('formshive_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'formshive'));
        }
        
        $form_id = intval($_POST['form_id']);
        $form = $this->db->get_form($form_id);
        
        if ($form) {
            wp_send_json_success($form);
        } else {
            wp_send_json_error(__('Form not found', 'formshive'));
        }
    }
    
    /**
     * AJAX: Duplicate form
     */
    public function ajax_duplicate_form() {
        check_ajax_referer('formshive_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'formshive'));
        }
        
        $form_id = intval($_POST['form_id']);
        $original_form = $this->db->get_form($form_id);
        
        if (!$original_form) {
            wp_send_json_error(__('Original form not found', 'formshive'));
        }
        
        // Create duplicate data
        $duplicate_data = array(
            'name' => $original_form['name'] . ' (Copy)',
            'form_id' => $original_form['form_id'], // Same Formshive form ID
            'type' => $original_form['type'],
            'framework' => $original_form['framework'],
            'form_data' => $original_form['form_data']
        );
        
        $result = $this->db->insert_form($duplicate_data);
        
        if ($result && !is_wp_error($result)) {
            wp_send_json_success(array(
                'new_form_id' => $result,
                'message' => __('Form duplicated successfully!', 'formshive')
            ));
        } else {
            $error_message = is_wp_error($result) ? $result->get_error_message() : __('Failed to duplicate form', 'formshive');
            wp_send_json_error($error_message);
        }
    }
    
    
}
