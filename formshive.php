<?php

/**
 * Plugin Name: Formshive
 * Plugin URI: https://formshive.com
 * Description: Easily embed and create Formshive forms on your WordPress website.
 * Version: 0.1.2
 * Author: Franz Geffke
 * Author URI: https://gofranz.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: formshive
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FORMSHIVE_VERSION', '0.1.2');
define('FORMSHIVE_PLUGIN_FILE', __FILE__);
define('FORMSHIVE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FORMSHIVE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FORMSHIVE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Formshive Plugin Class
 */
class Formshive_Plugin {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Load plugin textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Include required files
        $this->includes();
        
        // Initialize components
        add_action('init', array($this, 'init_components'));
        
        // Activation and deactivation hooks
        register_activation_hook(FORMSHIVE_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(FORMSHIVE_PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once FORMSHIVE_PLUGIN_DIR . 'includes/class-formshive-admin.php';
        require_once FORMSHIVE_PLUGIN_DIR . 'includes/class-formshive-database.php';
        require_once FORMSHIVE_PLUGIN_DIR . 'includes/class-formshive-shortcode.php';
        require_once FORMSHIVE_PLUGIN_DIR . 'includes/class-formshive-blocks.php';
        require_once FORMSHIVE_PLUGIN_DIR . 'includes/class-formshive-api.php';
    }
    
    /**
     * Initialize plugin components
     */
    public function init_components() {
        // Initialize database
        Formshive_Database::get_instance();
        
        // Initialize admin
        if (is_admin()) {
            Formshive_Admin::get_instance();
        }
        
        // Initialize shortcode
        Formshive_Shortcode::get_instance();
        
        // Initialize blocks
        Formshive_Blocks::get_instance();
        
        // Initialize API
        Formshive_API::get_instance();
        
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('formshive', false, dirname(FORMSHIVE_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        Formshive_Database::create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = array(
            'api_endpoint' => 'https://api.formshive.com/v1',
            'default_framework' => 'bootstrap',
            'enable_dev_mode' => false
        );
        
        add_option('formshive_settings', $default_options);
    }
}

// Initialize the plugin
Formshive_Plugin::get_instance();
