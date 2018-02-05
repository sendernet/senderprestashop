{*
 * 2010-2018 Sender.net
 *
 * Sender.net Integration module for prestahop
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 *}
{if $showForm and $formUrl}
<div>
    <div class="col-xs-4" id="senderFormContainer">
        <script type="text/javascript">
            jQuery(document).ready(function() { 
                jQuery('#sender-sub-main').detach();
                jQuery('#senderFormContainer').append(jQuery('.sender-sub-main'));
            });
        </script>
        <script type="text/javascript" src="{$formUrl|escape:'htmlall':'UTF-8'}"></script>
    </div>
</div>
{/if}

{if $showPushProject and $pushProject}
<script type="text/javascript">
(function(p, u, s, h) {
    p._spq = p._spq || [];
    p._spq.push(['_currentTime', Date.now()]);
    s = u.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = "{$pushProject|escape:'htmlall':'UTF-8'}";
    h = u.getElementsByTagName('script')[0];
    h.parentNode.insertBefore(s, h);
})(window, document);
</script>
{/if}