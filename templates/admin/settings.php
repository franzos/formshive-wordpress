<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$settings = wp_parse_args($settings, array(
    'api_endpoint' => 'https://api.formshive.com/v1',
    'default_framework' => 'formshive',
    'enable_dev_mode' => false
));
?>

<div class="wrap">
    <h1><?php esc_html_e('Formshive Settings', 'formshive'); ?></h1>
    
    <?php settings_errors('formshive_settings'); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('formshive_settings', 'formshive_settings_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="api-endpoint"><?php esc_html_e('API Endpoint', 'formshive'); ?></label>
                    </th>
                    <td>
                        <input type="url" 
                               id="api-endpoint" 
                               name="api_endpoint" 
                               class="regular-text" 
                               value="<?php echo esc_attr($settings['api_endpoint']); ?>" 
                               required>
                        <p class="description">
                            <?php esc_html_e('The Formshive API endpoint URL. Do not change unless instructed.', 'formshive'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default-framework"><?php esc_html_e('Default CSS Framework', 'formshive'); ?></label>
                    </th>
                    <td>
                        <select id="default-framework" name="default_framework">
                            <option value="formshive" <?php selected($settings['default_framework'], 'formshive'); ?>>
                                <?php esc_html_e('Formshive', 'formshive'); ?>
                            </option>
                            <option value="bootstrap" <?php selected($settings['default_framework'], 'bootstrap'); ?>>
                                <?php esc_html_e('Bootstrap', 'formshive'); ?>
                            </option>
                            <option value="bulma" <?php selected($settings['default_framework'], 'bulma'); ?>>
                                <?php esc_html_e('Bulma', 'formshive'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Default CSS framework for new embedded forms.', 'formshive'); ?>
                        </p>
                    </td>
                </tr>
                
                <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                <tr>
                    <th scope="row">
                        <label for="enable-dev-mode"><?php esc_html_e('Development Mode', 'formshive'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   id="enable-dev-mode" 
                                   name="enable_dev_mode" 
                                   value="1" 
                                   <?php checked($settings['enable_dev_mode']); ?>>
                            <?php esc_html_e('Enable development mode features', 'formshive'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Enables additional debugging and development features. Only visible when WP_DEBUG is enabled.', 'formshive'); ?>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h2><?php esc_html_e('Usage Instructions', 'formshive'); ?></h2>
        <div class="formshive-instructions">
            <h3><?php esc_html_e('Shortcode Usage', 'formshive'); ?></h3>
            <p><?php esc_html_e('Use the following shortcode to display your forms:', 'formshive'); ?></p>
            <code>[formshive id="123"]</code>
            <p><?php esc_html_e('Or by form ID:', 'formshive'); ?></p>
            <code>[formshive form_id="your-form-uuid"]</code>
            
            <h3><?php esc_html_e('Gutenberg Block', 'formshive'); ?></h3>
            <p><?php esc_html_e('Search for "Formshive" in the block inserter to add forms using the visual editor.', 'formshive'); ?></p>
            
            <h3><?php esc_html_e('PHP Template Usage', 'formshive'); ?></h3>
            <p><?php esc_html_e('Use in your theme templates:', 'formshive'); ?></p>
            <code>&lt;?php echo do_shortcode('[formshive id="123"]'); ?&gt;</code>
        </div>
        
        <p class="submit">
            <input type="submit" 
                   name="submit" 
                   id="submit" 
                   class="button button-primary" 
                   value="<?php esc_attr_e('Save Settings', 'formshive'); ?>">
        </p>
    </form>
</div>

<style>
.formshive-instructions {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}

.formshive-instructions h3 {
    margin-top: 0;
    color: #23282d;
}

.formshive-instructions code {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 4px 8px;
    font-family: Consolas, Monaco, monospace;
    font-size: 13px;
    display: inline-block;
    margin: 4px 0;
}

.formshive-instructions p {
    margin-bottom: 10px;
}
</style>
