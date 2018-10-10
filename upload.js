$( function () {





    jQuery('.get_upload_file').bind('submit', function(e){
        e.preventDefault();
        var form=jQuery(this);
        var m_method=jQuery(this).attr('method');
        var m_action=jQuery(this).attr('action');
        var m_data=jQuery(this).serialize();
        jQuery.ajax({
            type: m_method,
            url: m_action,
            data: m_data,
            dataType : 'json',
            success: function(result){
                if(result.success){
                    jQuery('.link_for_upload').html(result.success);
                    form.hide();
                }
                if(result.error){
                    alert(result.error) ;
                }
            }
        });

    });//submit-card
} );