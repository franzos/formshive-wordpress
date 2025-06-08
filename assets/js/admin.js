/**
 * Admin JavaScript for Formshive plugin
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Delete form functionality
        $('.formshive-delete-form').on('click', function() {
            var formId = $(this).data('form-id');
            var $row = $(this).closest('tr');
            
            if (!confirm(formshive_ajax.strings.confirm_delete)) {
                return;
            }
            
            $.post(ajaxurl, {
                action: 'formshive_delete_form',
                form_id: formId,
                nonce: formshive_ajax.nonce
            }, function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if table is empty
                        if ($('.wp-list-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.data || formshive_ajax.strings.error);
                }
            }).fail(function() {
                alert(formshive_ajax.strings.error);
            });
        });
        
        // Copy shortcode to clipboard
        $('.formshive-shortcode-input').on('click', function() {
            $(this).select();
            try {
                document.execCommand('copy');
                var $this = $(this);
                var originalBg = $this.css('background-color');
                $this.css('background-color', '#d4edda');
                setTimeout(function() {
                    $this.css('background-color', originalBg);
                }, 1000);
            } catch (err) {
                // Copy failed silently
            }
        });
    });


})(jQuery);
