<?php
/**
 * Database operations for Formshive plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Formshive_Database {
    
    private static $instance = null;
    private $table_name;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'formshive_forms';
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'formshive_forms';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            form_id varchar(255) NOT NULL,
            type enum('embed', 'create') NOT NULL DEFAULT 'embed',
            framework varchar(50) DEFAULT 'bootstrap',
            form_data longtext,
            status enum('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY form_id (form_id),
            KEY type (type),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Check if table was created successfully
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            error_log('Formshive: Failed to create database table');
            return false;
        }
        
        return true;
    }
    
    /**
     * Insert a new form
     */
    public function insert_form($data) {
        global $wpdb;
        
        $data = $this->validate_form_data($data);
        if (is_wp_error($data)) {
            return $data;
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'form_id' => sanitize_text_field($data['form_id']),
                'type' => sanitize_text_field($data['type']),
                'framework' => sanitize_text_field($data['framework']),
                'form_data' => wp_json_encode($data['form_data']),
                'status' => 'active'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            error_log('Formshive Database Error: ' . $wpdb->last_error);
            return new WP_Error('db_insert_error', __('Failed to save form to database', 'formshive'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update a form
     */
    public function update_form($id, $data) {
        global $wpdb;
        
        $data = $this->validate_form_data($data);
        if (is_wp_error($data)) {
            return $data;
        }
        
        $result = $wpdb->update(
            $this->table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'form_id' => sanitize_text_field($data['form_id']),
                'type' => sanitize_text_field($data['type']),
                'framework' => sanitize_text_field($data['framework']),
                'form_data' => wp_json_encode($data['form_data'])
            ),
            array('id' => intval($id)),
            array('%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            error_log('Formshive Database Error: ' . $wpdb->last_error);
            return new WP_Error('db_update_error', __('Failed to update form in database', 'formshive'));
        }
        
        return $result;
    }
    
    /**
     * Get a form by ID
     */
    public function get_form($id) {
        global $wpdb;
        
        $form = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $this->table_name WHERE id = %d",
            $id
        ), ARRAY_A);
        
        if ($form && $form['form_data']) {
            $form['form_data'] = json_decode($form['form_data'], true);
        }
        
        return $form;
    }
    
    /**
     * Get a form by form_id
     */
    public function get_form_by_form_id($form_id) {
        global $wpdb;
        
        $form = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $this->table_name WHERE form_id = %s",
            $form_id
        ), ARRAY_A);
        
        if ($form && $form['form_data']) {
            $form['form_data'] = json_decode($form['form_data'], true);
        }
        
        return $form;
    }
    
    /**
     * Get all forms
     */
    public function get_all_forms($status = 'active') {
        global $wpdb;
        
        $where_clause = '';
        if ($status) {
            $where_clause = $wpdb->prepare("WHERE status = %s", sanitize_text_field($status));
        }
        
        $forms = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY created_at DESC",
            ARRAY_A
        );
        
        if (!$forms) {
            return array();
        }
        
        foreach ($forms as &$form) {
            if (!empty($form['form_data'])) {
                $form['form_data'] = json_decode($form['form_data'], true);
            } else {
                $form['form_data'] = array();
            }
        }
        
        return $forms;
    }
    
    /**
     * Delete a form
     */
    public function delete_form($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => intval($id)),
            array('%d')
        );
        
        if ($result === false) {
            error_log('Formshive Database Error: ' . $wpdb->last_error);
            return new WP_Error('db_delete_error', __('Failed to delete form from database', 'formshive'));
        }
        
        return $result;
    }
    
    /**
     * Validate form data before database operations
     */
    private function validate_form_data($data) {
        $errors = array();
        
        // Required fields
        if (empty($data['name'])) {
            $errors[] = __('Form name is required', 'formshive');
        }
        
        if (empty($data['form_id'])) {
            $errors[] = __('Form ID is required', 'formshive');
        }
        
        if (empty($data['type']) || !in_array($data['type'], array('embed', 'create'))) {
            $errors[] = __('Valid form type is required', 'formshive');
        }
        
        // Validate form_id format (should be UUID)
        if (!empty($data['form_id']) && !preg_match('/^[a-f0-9\-]{36}$/i', $data['form_id'])) {
            $errors[] = __('Form ID must be a valid UUID format', 'formshive');
        }
        
        // Validate framework
        if (!empty($data['framework']) && !in_array($data['framework'], array('formshive', 'bootstrap', 'bulma'))) {
            $data['framework'] = 'formshive'; // Default fallback
        }
        
        // Ensure form_data is array
        if (!isset($data['form_data']) || !is_array($data['form_data'])) {
            $data['form_data'] = array();
        }
        
        // Validate form_data for 'create' type forms
        if ($data['type'] === 'create' && !empty($data['form_data']['fields'])) {
            $field_validation = Formshive_Form_Builder::validate_form_data($data['form_data']);
            if (!empty($field_validation)) {
                $errors = array_merge($errors, $field_validation);
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(', ', $errors));
        }
        
        return $data;
    }
    
    /**
     * Get form statistics
     */
    public function get_form_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total forms
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // Active forms
        $stats['active'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'active'");
        
        // Forms by type
        $stats['embed'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE type = 'embed'");
        $stats['create'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE type = 'create'");
        
        return $stats;
    }
    
    /**
     * Check if table exists
     */
    public function table_exists() {
        global $wpdb;
        return $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
    }
    
    /**
     * Drop tables (for uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'formshive_forms';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
    
    /**
     * Get table name
     */
    public function get_table_name() {
        return $this->table_name;
    }
}
