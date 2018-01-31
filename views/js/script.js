(function($) {
    'use strict';

    jQuery(document).ready(function() {


        var senderForm = jQuery('#sender-subscribe');

        if (senderForm.length > 0) {
            senderForm.find('button').on('click', function(event) {
                if (senderForm.find('#email').val().length > 1) {
                    console.log(senderForm.find('#email').val());
                    console.log(senderForm.find('#firstname').val());
                    console.log(senderForm.find('#lastname').val());
                }
            });
        }



        jQuery('#swToggleWidget').on('click', function(event) {

            jQuery('#swToggleWidget').text('Saving...');
            jQuery('#swToggleWidget').attr('disabled', true);

            jQuery.post(formsAjaxurl, { action: 'saveAllowForms' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
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

        jQuery('#swToggleGuestCartTracking').on('click', function(event) {

            jQuery('#swToggleGuestCartTracking').text('Saving...');
            jQuery('#swToggleGuestCartTracking').attr('disabled', true);

            jQuery.post(listsAjaxurl, { action: 'saveAllowGuestCartTracking' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    jQuery('#swToggleGuestCartTrackingTitle').text('disabled');
                    jQuery('#swToggleGuestCartTrackingTitle').css('color', 'red');
                    jQuery('#swToggleGuestCartTracking').text('Enable');
                    jQuery('#swToggleGuestCartTracking').css('background-color', 'green');
                    $('#guests_lists').addClass('hidden');
                } else {
                    jQuery('#swToggleGuestCartTrackingTitle').text('enabled');
                    jQuery('#swToggleGuestCartTrackingTitle').css('color', 'green');
                    jQuery('#swToggleGuestCartTracking').text('Disable');
                    jQuery('#swToggleGuestCartTracking').css('background-color', 'red');
                    $('#guests_lists').removeClass('hidden');
                }

                jQuery('#swToggleGuestCartTracking').removeAttr('disabled');

            });

        });

        jQuery('#swTogglePush').on('click', function(event) {
            console.log(true)

            jQuery('#swTogglePush').text('Saving...');
            jQuery('#swTogglePush').attr('disabled', true);

            jQuery.post(pushAjaxurl, { action: 'saveAllowPush' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    jQuery('#swTogglePushTitle').text('disabled');
                    jQuery('#swTogglePushTitle').css('color', 'red');
                    jQuery('#swTogglePush').text('Enable');
                    jQuery('#swTogglePush').css('background-color', 'green');
                    $('#push_project').addClass('hidden');
                } else {
                    jQuery('#swTogglePushTitle').text('enabled');
                    jQuery('#swTogglePushTitle').css('color', 'green');
                    jQuery('#swTogglePush').text('Disable');
                    jQuery('#swTogglePush').css('background-color', 'red');
                    $('#push_project').removeClass('hidden');
                }

                jQuery('#swTogglePush').removeAttr('disabled');

            });

        });

        jQuery('#swFormsSelect').on('change', function(event) {

            jQuery('#swFormsSelect').attr('disabled', true);

            jQuery.post(formsAjaxurl, { action: 'saveFormId', form_id: jQuery('#swFormsSelect').val() }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    console.log('save error');
                } else {
                    console.log('save success');
                }

                jQuery('#swFormsSelect').removeAttr('disabled');

            });

        });

        jQuery('#swGuestListSelect').on('change', function(event) {

            jQuery('#swGuestListSelect').attr('disabled', true);

            jQuery.post(listsAjaxurl, { action: 'saveGuestListId', list_id: jQuery('#swGuestListSelect').val() }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    console.log('save error');
                } else {
                    console.log('save success');
                }

                jQuery('#swGuestListSelect').removeAttr('disabled');

            });

        });

        jQuery('#swCustomerListSelect').on('change', function(event) {

            jQuery('#swCustomerListSelect').attr('disabled', true);

            jQuery.post(listsAjaxurl, { action: 'saveCustomerListId', list_id: jQuery('#swCustomerListSelect').val() }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    console.log('save error');
                } else {
                    console.log('save success');
                }

                jQuery('#swCustomerListSelect').removeAttr('disabled');

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