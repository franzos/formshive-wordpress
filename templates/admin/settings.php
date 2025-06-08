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
    <h1><?php _e('Formshive Settings', 'formshive'); ?></h1>
    
    <?php settings_errors('formshive_settings'); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('formshive_settings', 'formshive_settings_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="api-endpoint"><?php _e('API Endpoint', 'formshive'); ?></label>
                    </th>
                    <td>
                        <input type="url" 
                               id="api-endpoint" 
                               name="api_endpoint" 
                               class="regular-text" 
                               value="<?php echo esc_attr($settings['api_endpoint']); ?>" 
                               required>
                        <p class="description">
                            <?php _e('The Formshive API endpoint URL. Do not change unless instructed.', 'formshive'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default-framework"><?php _e('Default CSS Framework', 'formshive'); ?></label>
                    </th>
                    <td>
                        <select id="default-framework" name="default_framework">
                            <option value="formshive" <?php selected($settings['default_framework'], 'formshive'); ?>>
                                <?php _e('Formshive', 'formshive'); ?>
                            </option>
                            <option value="bootstrap" <?php selected($settings['default_framework'], 'bootstrap'); ?>>
                                <?php _e('Bootstrap', 'formshive'); ?>
                            </option>
                            <option value="bulma" <?php selected($settings['default_framework'], 'bulma'); ?>>
                                <?php _e('Bulma', 'formshive'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php _e('Default CSS framework for new embedded forms.', 'formshive'); ?>
                        </p>
                    </td>
                </tr>
                
                <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
                <tr>
                    <th scope="row">
                        <label for="enable-dev-mode"><?php _e('Development Mode', 'formshive'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   id="enable-dev-mode" 
                                   name="enable_dev_mode" 
                                   value="1" 
                                   <?php checked($settings['enable_dev_mode']); ?>>
                            <?php _e('Enable development mode features', 'formshive'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Enables additional debugging and development features. Only visible when WP_DEBUG is enabled.', 'formshive'); ?>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h2><?php _e('Usage Instructions', 'formshive'); ?></h2>
        <div class="formshive-instructions">
            <h3><?php _e('Shortcode Usage', 'formshive'); ?></h3>
            <p><?php _e('Use the following shortcode to display your forms:', 'formshive'); ?></p>
            <code>[formshive id="123"]</code>
            <p><?php _e('Or by form ID:', 'formshive'); ?></p>
            <code>[formshive form_id="your-form-uuid"]</code>
            
            <h3><?php _e('Gutenberg Block', 'formshive'); ?></h3>
            <p><?php _e('Search for "Formshive" in the block inserter to add forms using the visual editor.', 'formshive'); ?></p>
            
            <h3><?php _e('PHP Template Usage', 'formshive'); ?></h3>
            <p><?php _e('Use in your theme templates:', 'formshive'); ?></p>
            <code>&lt;?php echo do_shortcode('[formshive id="123"]'); ?&gt;</code>
        </div>
        
        <p class="submit">
            <input type="submit" 
                   name="submit" 
                   id="submit" 
                   class="button button-primary" 
                   value="<?php _e('Save Settings', 'formshive'); ?>">
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
