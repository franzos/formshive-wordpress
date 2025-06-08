<?php
/**
 * Shortcode functionality for Formshive plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Formshive_Shortcode {
    
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
        add_shortcode('formshive', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue frontend scripts only when needed
     */
    public function enqueue_scripts() {
        // Only enqueue scripts if we have forms on this page
        if (!$this->has_forms_on_page()) {
            return;
        }
        
        wp_enqueue_script(
            'formshive-frontend',
            FORMSHIVE_PLUGIN_URL . 'assets/js/frontend.js',
            array(),
            FORMSHIVE_VERSION,
            true
        );
        
        $settings = get_option('formshive_settings', array());
        wp_localize_script('formshive-frontend', 'formshive_config', array(
            'api_endpoint' => $settings['api_endpoint'] ?? 'https://api.formshive.com/v1'
        ));
    }
    
    /**
     * Check if current page/post has Formshive forms
     */
    private function has_forms_on_page() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check for shortcode in post content
        if (has_shortcode($post->post_content, 'formshive')) {
            return true;
        }
        
        // Check for Gutenberg blocks
        if (function_exists('has_block') && has_block('formshive/form', $post)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle formshive shortcode
     * Usage: [formshive id="123"] or [formshive form_id="uuid-here"]
     */
    public function shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'form_id' => '',
            'framework' => ''
        ), $atts, 'formshive');
        
        // Get form data
        $form = null;
        if (!empty($atts['id'])) {
            $form = $this->db->get_form(intval($atts['id']));
        } elseif (!empty($atts['form_id'])) {
            $form = $this->db->get_form_by_form_id(sanitize_text_field($atts['form_id']));
        }
        
        if (!$form) {
            return '<div class="formshive-error">' . __('Form not found.', 'formshive') . '</div>';
        }
        
        // Override framework if specified in shortcode
        $framework = !empty($atts['framework']) ? sanitize_text_field($atts['framework']) : $form['framework'];
        
        if ($form['type'] === 'embed') {
            return $this->render_embed_form($form, $framework);
        } else {
            return $this->render_create_form($form);
        }
    }
    
    /**
     * Render embedded form
     */
    private function render_embed_form($form, $framework) {
        $unique_id = 'formshive-' . uniqid();
        
        ob_start();
        ?>
        <div id="<?php echo esc_attr($unique_id); ?>" 
             class="formshive-embed" 
             data-form-id="<?php echo esc_attr($form['form_id']); ?>" 
             data-framework="<?php echo esc_attr($framework); ?>">
            <div class="formshive-loading">
                <?php esc_html_e('Loading form...', 'formshive'); ?>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.FormshiveUtils !== 'undefined') {
                const container = document.querySelector('#<?php echo esc_js($unique_id); ?>');
                window.FormshiveUtils.loadFormshiveForm({
                    formId: '<?php echo esc_js($form['form_id']); ?>',
                    framework: '<?php echo esc_js($framework); ?>',
                    apiEndpoint: formshive_config.api_endpoint,
                    rustyFormsDiv: container
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render manually created form
     */
    private function render_create_form($form) {
        if (empty($form['form_data']['fields'])) {
            return '<div class="formshive-error">' . __('No form fields configured.', 'formshive') . '</div>';
        }
        
        $unique_id = 'formshive-form-' . uniqid();
        
        ob_start();
        ?>
        <form id="<?php echo esc_attr($unique_id); ?>" 
              class="formshive-created-form" 
              data-form-id="<?php echo esc_attr($form['form_id']); ?>"
              action="<?php echo esc_url($this->get_form_action_url($form['form_id'])); ?>"
              method="post">
            
            <?php foreach ($form['form_data']['fields'] as $field): ?>
                <div class="formshive-field-wrapper">
                    <?php echo wp_kses_post($this->render_field($field)); ?>
                </div>
            <?php endforeach; ?>
            
            <div class="formshive-submit-wrapper">
                <button type="submit" class="formshive-submit-btn">
                    <?php echo esc_html($form['form_data']['submit_text'] ?? __('Submit', 'formshive')); ?>
                </button>
            </div>
        </form>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get form action URL for Formshive API
     */
    private function get_form_action_url($form_id) {
        $settings = get_option('formshive_settings', array());
        $api_endpoint = $settings['api_endpoint'] ?? 'https://api.formshive.com/v1';
        
        return rtrim($api_endpoint, '/') . '/forms/' . $form_id . '/submit';
    }
    
    /**
     * Render individual form field
     */
    private function render_field($field) {
        $field_id = 'field_' . uniqid();
        $required = !empty($field['required']) ? 'required' : '';
        $required_mark = !empty($field['required']) ? ' <span class="required">*</span>' : '';
        
        ob_start();
        ?>
        <div class="formshive-field formshive-field-<?php echo esc_attr($field['type']); ?>">
            <?php if (!empty($field['label'])): ?>
                <label for="<?php echo esc_attr($field_id); ?>" class="formshive-label">
                    <?php echo esc_html($field['label']); ?><?php echo wp_kses_post($required_mark); ?>
                </label>
            <?php endif; ?>
            
            <?php switch ($field['type']):
                case 'text':
                case 'email':
                case 'tel':
                case 'url':
                case 'number':
                case 'date':
                case 'datetime':
                case 'time': ?>
                    <input type="<?php echo esc_attr($field['type']); ?>" 
                           id="<?php echo esc_attr($field_id); ?>"
                           name="<?php echo esc_attr($field['name']); ?>" 
                           placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                           class="formshive-input"
                           <?php echo esc_attr($required); ?>>
                    <?php break;
                    
                case 'textarea': ?>
                    <textarea id="<?php echo esc_attr($field_id); ?>"
                              name="<?php echo esc_attr($field['name']); ?>" 
                              placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                              class="formshive-textarea"
                              rows="<?php echo esc_attr($field['rows'] ?? 4); ?>"
                              <?php echo esc_attr($required); ?>></textarea>
                    <?php break;
                    
                case 'select': ?>
                    <select id="<?php echo esc_attr($field_id); ?>"
                            name="<?php echo esc_attr($field['name']); ?>" 
                            class="formshive-select"
                            <?php echo esc_attr($required); ?>>
                        <?php if (!empty($field['placeholder'])): ?>
                            <option value=""><?php echo esc_html($field['placeholder']); ?></option>
                        <?php endif; ?>
                        <?php if (!empty($field['options'])): ?>
                            <?php foreach ($field['options'] as $option): ?>
                                <option value="<?php echo esc_attr($option['value']); ?>">
                                    <?php echo esc_html($option['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php break;
                    
                case 'radio': ?>
                    <?php if (!empty($field['options'])): ?>
                        <div class="formshive-radio-group">
                            <?php foreach ($field['options'] as $i => $option): ?>
                                <label class="formshive-radio-label">
                                    <input type="radio" 
                                           name="<?php echo esc_attr($field['name']); ?>" 
                                           value="<?php echo esc_attr($option['value']); ?>"
                                           class="formshive-radio"
                                           <?php echo esc_attr($required); ?>>
                                    <?php echo esc_html($option['label']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'checkbox': ?>
                    <?php if (!empty($field['options'])): ?>
                        <div class="formshive-checkbox-group">
                            <?php foreach ($field['options'] as $i => $option): ?>
                                <label class="formshive-checkbox-label">
                                    <input type="checkbox" 
                                           name="<?php echo esc_attr($field['name']); ?>[]" 
                                           value="<?php echo esc_attr($option['value']); ?>"
                                           class="formshive-checkbox">
                                    <?php echo esc_html($option['label']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php break;
                    
                case 'file': ?>
                    <input type="file" 
                           id="<?php echo esc_attr($field_id); ?>"
                           name="<?php echo esc_attr($field['name']); ?>" 
                           class="formshive-file"
                           <?php echo esc_attr($required); ?>
                           <?php if (!empty($field['accept'])): ?>accept="<?php echo esc_attr($field['accept']); ?>"<?php endif; ?>>
                    <?php break;
                    
            endswitch; ?>
            
            <?php if (!empty($field['description'])): ?>
                <small class="formshive-field-description">
                    <?php echo esc_html($field['description']); ?>
                </small>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
