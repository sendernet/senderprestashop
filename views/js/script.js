(function( $ ) {
	'use strict';
    
     jQuery(document).ready(function(){
         
        jQuery('#swToggleWidget').on('click', function (event) {
            
            jQuery('#swToggleWidget').text('Saving...');
            jQuery('#swToggleWidget').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'saveAllowForms' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if(!proceed.result) {
                    jQuery('#swToggleWidgetTitle').text('disabled');
                    jQuery('#swToggleWidgetTitle').css('color', 'red');
                    jQuery('#swToggleWidget').text('Enable');
                    jQuery('#swToggleWidget').css('background-color', 'green');
                    $('#forms_tab').addClass('hidden');
                } else {
                    jQuery('#swToggleWidgetTitle').text('enabled');
                    jQuery('#swToggleWidgetTitle').css('color', 'green');
                    jQuery('#swToggleWidget').text('Disable');
                    jQuery('#swToggleWidget').css('background-color', 'red');
                    $('#forms_tab').removeClass('hidden');
                }

                jQuery('#swToggleWidget').removeAttr('disabled');
                
            });
            
        });
        
        jQuery('#swFormsSelect').on('change', function (event) {
            
            jQuery('#swFormsSelect').attr('disabled', true);

            jQuery.post(ajaxurl, { action: 'saveFormId', form_id: jQuery('#swFormsSelect').val() }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if(!proceed.result) {
                    console.log('save error');
                } else {
                    console.log('save success');
                }

                jQuery('#swFormsSelect').removeAttr('disabled');
                
            });
            
        });
        /**
         * Tab menu change handler
         */
        jQuery('ul.sw-tabs li').click(function() {
            var tab_id = jQuery(this).data().tab;
            jQuery('ul.sw-tabs li').removeClass('sw-current').removeClass('sw-active');
            jQuery('.sw-tab-content').removeClass('sw-current');
            jQuery("#" + tab_id).addClass('sw-current');
            jQuery(this).addClass('sw-current').addClass('sw-active');
        })

        if (window.location.hash) {
            var hash = window.location.hash.substring(2);
            jQuery('[data-tab="' + hash + '"]').trigger('click');

        } else {}

    })

})(jQuery);