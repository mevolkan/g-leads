jQuery(document).ready(function($) {
    $('.editable').on('dblclick', function() {
        var $this = $(this);
        var originalContent = $this.text();
        var input = $('<input>', {
            type: 'text',
            value: originalContent,
            blur: function() {
                var newValue = $(this).val();
                if (newValue !== originalContent) {
                    $.post(ajaxurl, {
                        action: 'update_lead',
                        id: $this.data('id'),
                        column: $this.data('column'),
                        value: newValue,
                        // _ajax_nonce: '<?php echo wp_create_nonce('update_lead_action'); ?>'
                    }, function(response) {
                        if (response.success) {
                            $this.text(newValue);
                        } else {
                            alert('Error updating record');
                        }
                    });
                }
                $(this).remove();
                $this.show();
            }
        }).on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $(this).blur();
            }
        });
        $this.hide().after(input);
        input.focus();
    });

    //delete confirmation
    $('.delete-lead').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url');

        if (confirm('Are you sure you want to delete this lead?')) {
            window.location.href = url;
        }
    });
});