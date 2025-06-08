<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Formshive Forms', 'formshive'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=formshive-add-new')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'formshive'); ?>
    </a>
    
    <?php if (empty($forms)): ?>
        <div class="notice notice-info">
            <p>
                <?php esc_html_e('No forms found.', 'formshive'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=formshive-add-new')); ?>">
                    <?php esc_html_e('Create your first form', 'formshive'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Name', 'formshive'); ?></th>
                    <th scope="col"><?php esc_html_e('Form ID', 'formshive'); ?></th>
                    <th scope="col"><?php esc_html_e('Type', 'formshive'); ?></th>
                    <th scope="col"><?php esc_html_e('Framework', 'formshive'); ?></th>
                    <th scope="col"><?php esc_html_e('Shortcode', 'formshive'); ?></th>
                    <th scope="col"><?php esc_html_e('Created', 'formshive'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'formshive'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $form): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=formshive-add-new&edit=' . $form['id'])); ?>">
                                    <?php echo esc_html($form['name']); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <code><?php echo esc_html($form['form_id']); ?></code>
                        </td>
                        <td>
                            <span class="formshive-type-badge formshive-type-<?php echo esc_attr($form['type']); ?>">
                                <?php echo esc_html(ucfirst($form['type'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($form['type'] === 'embed'): ?>
                                <?php echo esc_html(ucfirst($form['framework'])); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-minus"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="text" 
                                   class="formshive-shortcode-input" 
                                   value="[formshive id=&quot;<?php echo esc_attr($form['id']); ?>&quot;]" 
                                   readonly 
                                   onclick="this.select();">
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($form['created_at']))); ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=formshive-add-new&edit=' . $form['id'])); ?>" 
                               class="button button-small">
                                <?php esc_html_e('Edit', 'formshive'); ?>
                            </a>
                            <button type="button" 
                                    class="button button-small button-link-delete formshive-delete-form" 
                                    data-form-id="<?php echo esc_attr($form['id']); ?>">
                                <?php esc_html_e('Delete', 'formshive'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.formshive-type-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.formshive-type-embed {
    background-color: #e1f5fe;
    color: #0277bd;
}

.formshive-type-create {
    background-color: #f3e5f5;
    color: #7b1fa2;
}

.formshive-shortcode-input {
    width: 100%;
    font-family: monospace;
    font-size: 12px;
    padding: 4px 6px;
    border: 1px solid #ddd;
    border-radius: 3px;
    background: #f9f9f9;
}

.formshive-shortcode-input:focus {
    background: #fff;
    border-color: #0073aa;
}
</style>
