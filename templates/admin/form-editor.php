<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = $form !== null;
$page_title = $is_edit ? __('Edit Form', 'formshive') : __('Add New Form', 'formshive');
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form id="formshive-form-editor" method="post" action="">
        <?php wp_nonce_field('formshive_save_form', 'formshive_nonce'); ?>
        
        <input type="hidden" id="form-id" name="form_id" value="<?php echo $is_edit ? esc_attr($form['id']) : ''; ?>">
        <input type="hidden" name="type" value="embed">
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="form-name"><?php _e('Form Name', 'formshive'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="form-name" 
                               name="name" 
                               class="regular-text" 
                               value="<?php echo $is_edit ? esc_attr($form['name']) : ''; ?>" 
                               required>
                        <p class="description"><?php _e('Enter a descriptive name for this form.', 'formshive'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="formshive-url"><?php _e('Formshive URL', 'formshive'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="formshive-url" 
                               name="formshive_url" 
                               class="regular-text" 
                               value="<?php echo $is_edit ? esc_attr('https://api.formshive.com/v1/digest/' . $form['form_id']) : ''; ?>" 
                               placeholder="https://api.formshive.com/v1/digest/2ce22659-397b-412c-abe8-a64ce53dc4a0">
                        <input type="hidden" id="formshive-form-id" name="formshive_form_id" value="<?php echo $is_edit ? esc_attr($form['form_id']) : ''; ?>">
                        <p class="description">
                            <?php _e('Enter the complete Formshive form URL from your Formshive account.', 'formshive'); ?>
                        </p>
                        <div id="form-validation-result"></div>
                    </td>
                </tr>
                
            </tbody>
        </table>
        
        <!-- Embed Options -->
        <div id="embed-options">
            <h2><?php _e('Embed Options', 'formshive'); ?></h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="framework"><?php _e('CSS Framework', 'formshive'); ?></label>
                        </th>
                        <td>
                            <select id="framework" name="framework">
                                <option value="formshive" <?php echo ($is_edit && $form['framework'] === 'formshive') ? 'selected' : ''; ?>>
                                    <?php _e('Formshive', 'formshive'); ?>
                                </option>
                                <option value="bootstrap" <?php echo ($is_edit && $form['framework'] === 'bootstrap') ? 'selected' : ''; ?>>
                                    <?php _e('Bootstrap', 'formshive'); ?>
                                </option>
                                <option value="bulma" <?php echo ($is_edit && $form['framework'] === 'bulma') ? 'selected' : ''; ?>>
                                    <?php _e('Bulma', 'formshive'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Select the CSS framework for styling the embedded form.', 'formshive'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        
        <p class="submit">
            <input type="submit" 
                   name="submit" 
                   id="submit" 
                   class="button button-primary" 
                   value="<?php echo $is_edit ? __('Update Form', 'formshive') : __('Save Form', 'formshive'); ?>">
            <a href="<?php echo esc_url(admin_url('admin.php?page=formshive')); ?>" class="button">
                <?php _e('Cancel', 'formshive'); ?>
            </a>
        </p>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Validate Formshive URL
    $('#formshive-url').on('blur', function() {
        var url = $(this).val().trim();
        if (url) {
            validateFormshiveUrl(url);
        }
    });
    
    // Form submission
    $('#formshive-form-editor').on('submit', function(e) {
        e.preventDefault();
        saveForm();
    });
    
    function validateFormshiveUrl(url) {
        $('#form-validation-result').html('<span class="spinner is-active"></span> <?php _e('Validating...', 'formshive'); ?>');
        
        $.post(formshive_ajax.ajax_url, {
            action: 'formshive_validate_form_url',
            url: url,
            nonce: formshive_ajax.nonce
        }, function(response) {
            var $result = $('#form-validation-result');
            
            if (response.success) {
                $result.html('<span class="notice notice-success inline"><p>' + response.data.message + '</p></span>');
                $('#formshive-form-id').val(response.data.form_id);
            } else {
                $result.html('<span class="notice notice-error inline"><p>' + response.data + '</p></span>');
                $('#formshive-form-id').val('');
            }
        }).fail(function() {
            $('#form-validation-result').html('<span class="notice notice-error inline"><p><?php _e('Validation failed. Please try again.', 'formshive'); ?></p></span>');
        });
    }
    
    function saveForm() {
        var formData = {
            action: 'formshive_save_form',
            nonce: formshive_ajax.nonce,
            form_id: $('#form-id').val(),
            name: $('#form-name').val(),
            formshive_form_id: $('#formshive-form-id').val(),
            type: $('input[name="type"]').val(),
            framework: $('#framework').val(),
            form_data: '{}'
        };
        
        // Show saving state
        var $submitBtn = $('#submit');
        var originalText = $submitBtn.val();
        $submitBtn.val('<?php _e('Saving...', 'formshive'); ?>').prop('disabled', true);
        
        $.post(formshive_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                window.location.href = '<?php echo esc_url(admin_url('admin.php?page=formshive')); ?>';
            } else {
                alert(response.data || 'Save failed');
                $submitBtn.val(originalText).prop('disabled', false);
            }
        }).fail(function() {
            alert('<?php _e('Save failed. Please try again.', 'formshive'); ?>');
            $submitBtn.val(originalText).prop('disabled', false);
        });
    }
});
</script>
