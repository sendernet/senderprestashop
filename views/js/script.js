(function($) {
    'use strict';

    jQuery(document).ready(function() {
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