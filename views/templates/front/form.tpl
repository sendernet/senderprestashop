{*
 * Form and Push notificaton
 * template view
 *
 *}

{if $showForm and $formUrl}
<div>
    <div class="col-xs-4">
        <script type="text/javascript" src="{$formUrl}"></script>
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
    s.src = "{$pushProject}";
    h = u.getElementsByTagName('script')[0];
    h.parentNode.insertBefore(s, h);
})(window, document);
</script>
{/if}