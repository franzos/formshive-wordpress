<?php
/**
 * API functionality for Formshive plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Formshive_API {
    
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
        add_action('wp_ajax_formshive_validate_form_url', array($this, 'ajax_validate_form_url'));
    }
    
    
    /**
     * AJAX: Validate form URL and extract form ID
     */
    public function ajax_validate_form_url() {
        check_ajax_referer('formshive_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions', 'formshive'));
        }
        
        $url = sanitize_url($_POST['url']);
        $form_id = $this->extract_form_id_from_url($url);
        
        if (!$form_id) {
            wp_send_json_error(__('Invalid Formshive URL. Please enter the complete URL from your Formshive account (e.g., https://api.formshive.com/v1/digest/your-form-id).', 'formshive'));
        }
        
        // Validate form exists by checking API
        $settings = get_option('formshive_settings', array());
        $api_endpoint = $settings['api_endpoint'] ?? 'https://api.formshive.com/v1';
        
        $validation_result = $this->validate_form_html($api_endpoint, $form_id);
        
        if (!$validation_result['exists']) {
            wp_send_json_error(__('Form not found on Formshive. Please check the form ID.', 'formshive'));
        }
        
        if (!$validation_result['has_valid_html']) {
            wp_send_json_error(__('Form requires configuration. Please go back to the Formshive website, add Form Fields and activate them. Check Form Fields to complete setup.', 'formshive'));
        }
        
        wp_send_json_success(array(
            'form_id' => $form_id,
            'message' => __('Form validated successfully!', 'formshive')
        ));
    }
    
    /**
     * Extract form ID from Formshive URL
     */
    private function extract_form_id_from_url($url) {
        // Only accept full URLs with the pattern: https://api.formshive.com/v1/digest/{form-id}
        if (preg_match('/https?:\/\/[^\/]+\/v1\/digest\/([a-f0-9\-]{36})$/i', $url, $matches)) {
            return $matches[1];
        }
        
        return false;
    }
    
    
    /**
     * Validate if form exists and has valid HTML content
     */
    private function validate_form_html($api_endpoint, $form_id) {
        $url = rtrim($api_endpoint, '/') . '/forms/' . $form_id . '/html';
        $url = add_query_arg('iframe', 'false', $url);
        
        $args = array(
            'method' => 'GET',
            'timeout' => 15
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'exists' => false,
                'has_valid_html' => false
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            return array(
                'exists' => false,
                'has_valid_html' => false
            );
        }
        
        // Check if the HTML content indicates missing form fields
        $html_content = wp_remote_retrieve_body($response);
        $has_valid_html = !$this->is_form_check_specs_required($html_content);
        
        return array(
            'exists' => true,
            'has_valid_html' => $has_valid_html
        );
    }
    
    /**
     * Check if HTML content indicates form fields are required
     */
    private function is_form_check_specs_required($html_content) {
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html_content, $matches)) {
            $title = trim($matches[1]);
            return stripos($title, 'Form Check Specs Required') !== false;
        }
        
        return false;
    }
    
    /**
     * Get form HTML from Formshive API with caching
     */
    public function get_form_html($form_id, $framework = 'bootstrap') {
        $cache_key = 'formshive_html_' . md5($form_id . '_' . $framework);
        $cached_html = get_transient($cache_key);
        
        if ($cached_html !== false) {
            return $cached_html;
        }
        
        $settings = get_option('formshive_settings', array());
        $api_endpoint = $settings['api_endpoint'] ?? 'https://api.formshive.com/v1';
        
        $url = rtrim($api_endpoint, '/') . '/forms/' . $form_id . '/html';
        $url = add_query_arg(array(
            'iframe' => 'false',
            'css_framework' => $framework
        ), $url);
        
        $args = array(
            'method' => 'GET',
            'timeout' => 15
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return false;
        }
        
        $html = wp_remote_retrieve_body($response);
        
        // Cache for 1 hour
        set_transient($cache_key, $html, HOUR_IN_SECONDS);
        
        // Track this transient key for easier cleanup
        $this->track_transient_key($cache_key);
        
        return $html;
    }
    
    /**
     * Clear form HTML cache
     */
    public function clear_form_cache($form_id = null) {
        if ($form_id) {
            // Clear specific form cache for all frameworks
            $frameworks = array('bootstrap', 'bulma');
            foreach ($frameworks as $framework) {
                $cache_key = 'formshive_html_' . md5($form_id . '_' . $framework);
                delete_transient($cache_key);
            }
        } else {
            // Clear all form caches using WordPress functions
            $this->clear_all_formshive_transients();
        }
    }
    
    /**
     * Track transient keys for easier cleanup
     */
    private function track_transient_key($key) {
        $transient_keys = wp_cache_get('formshive_transient_keys', 'formshive');
        if (!$transient_keys) {
            $transient_keys = array();
        }
        
        if (!in_array($key, $transient_keys)) {
            $transient_keys[] = $key;
            wp_cache_set('formshive_transient_keys', $transient_keys, 'formshive', HOUR_IN_SECONDS);
        }
    }
    
    /**
     * Clear all formshive transients using WordPress functions
     */
    private function clear_all_formshive_transients() {
        // Get all transients with our prefix
        $transient_keys = wp_cache_get('formshive_transient_keys', 'formshive');
        if (!$transient_keys) {
            $transient_keys = array();
        }
        
        // Delete all stored transient keys
        foreach ($transient_keys as $key) {
            delete_transient($key);
        }
        
        // Clear the tracking cache
        wp_cache_delete('formshive_transient_keys', 'formshive');
        
        // As fallback, get options and clear manually (less efficient but WordPress-compliant)
        $options = wp_load_alloptions();
        foreach ($options as $option => $value) {
            if (strpos($option, '_transient_formshive_html_') === 0 || 
                strpos($option, '_transient_timeout_formshive_html_') === 0) {
                $transient_name = str_replace(array('_transient_', '_transient_timeout_'), '', $option);
                delete_transient($transient_name);
            }
        }
    }
    
}
