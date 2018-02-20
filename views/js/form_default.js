/**
 * 2010-2018 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

(function($) {
'use strict';

jQuery(document).ready(function() {

        console.log(forDefaultAjaxUrl);

        var senderForm = jQuery('#sender-subscribe');

        if (senderForm.length > 0) {
            senderForm.find('button').on('click', function(event) {
                if (senderForm.find('#email').val().length > 1) {
                    console.log(senderForm.find('#email').val());
                    console.log(senderForm.find('#firstname').val());
                    console.log(senderForm.find('#lastname').val());
                    subscribeForNewsLetter();
                }
            });
        }

        function subscribeForNewsLetter() {
            jQuery.post(forDefaultAjaxUrl, { action: 'subscribeForNewsLetter', data: {emai:'email@sad'} }, function(response) {
                var proceed = jQuery.parseJSON(response);

                console.log(proceed);

            });
        }
}

})

})(jQuery);