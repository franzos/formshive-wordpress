<?php
/**
 * Gutenberg blocks for Formshive plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Formshive_Blocks {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        add_action('init', array($this, 'register_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
    }
    
    /**
     * Register Gutenberg blocks
     */
    public function register_blocks() {
        if (!function_exists('register_block_type')) {
            return;
        }
        
        register_block_type('formshive/form', array(
            'editor_script' => 'formshive-blocks',
            'render_callback' => array($this, 'render_form_block'),
            'attributes' => array(
                'formId' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'framework' => array(
                    'type' => 'string',
                    'default' => 'bootstrap'
                )
            )
        ));
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'formshive-blocks',
            FORMSHIVE_PLUGIN_URL . 'assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            FORMSHIVE_VERSION,
            true
        );
        
        wp_enqueue_style(
            'formshive-blocks-editor',
            FORMSHIVE_PLUGIN_URL . 'assets/css/blocks-editor.css',
            array('wp-edit-blocks'),
            FORMSHIVE_VERSION
        );
        
        // Get all forms for the block editor
        $db = Formshive_Database::get_instance();
        $forms = $db->get_all_forms();
        
        $forms_data = array();
        foreach ($forms as $form) {
            $forms_data[] = array(
                'id' => $form['id'],
                'name' => $form['name'],
                'form_id' => $form['form_id'],
                'type' => $form['type']
            );
        }
        
        wp_localize_script('formshive-blocks', 'formshive_blocks', array(
            'forms' => $forms_data
        ));
    }
    
    /**
     * Render form block
     */
    public function render_form_block($attributes) {
        if (empty($attributes['formId'])) {
            return '<div class="formshive-block-placeholder">' . 
                   __('Please select a form in the block settings.', 'formshive') . 
                   '</div>';
        }
        
        $shortcode_atts = array(
            'id' => $attributes['formId']
        );
        
        if (!empty($attributes['framework'])) {
            $shortcode_atts['framework'] = $attributes['framework'];
        }
        
        $shortcode = Formshive_Shortcode::get_instance();
        return $shortcode->shortcode($shortcode_atts);
    }
}
