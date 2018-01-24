<div class="pure-g sender-prestashop-card">
    <div class="pure-u-1-1 sender-prestashop-header">
        <div class="pure-g">
            <div class="pure-u-1-1 pure-u-sm-1-2 sw-text-left">
                <img src="{$imageUrl}" alt="Sender Logo">
                <span>
                    <small style="vertical-align: bottom;">v{$moduleVersion}</small>
                </span>
            </div>
        </div>
    </div>
    
    <div class="pure-u-1-1 pure-u-lg-3-24 sender-prestashop-hide-small sender-prestashop-menu">
        <ul class="sw-tabs sw-main-menu">
            <li class="tab-link" data-tab="sw-push" disabled>
                <a href="#!sw-push"><i class="zmdi zmdi-notifications-active"></i> Push notifications</a>
            </li>
            <li data-tab="forms" class="tab-link">
                <a href="#!forms"><i class="zmdi zmdi-format-list-bulleted"></i> Forms</a>
            </li>

            <li data-tab="settings" id="workflows" class="tab-link">
                <a href="#!settings"><i class="zmdi zmdi-settings"></i> Settings</a>
            </li>
            
        </ul>
    </div>
    <div class="pure-u-1-1 pure-u-lg-18-24 sender-prestashop-content">
        <div id="forms" class="sw-tab-content <?php echo !get_option('sender_woocommerce_has_woocommerce') ? 'sw-current' : '';?>">
            <h1>One form for you</h1>
            <div class="col-xs-12">
                <script type="text/javascript" src="{$formUrl}"></script>
            </div>
        </div>
        <div id="sw-push" class="sw-tab-content">
            No no, push is no here
        </div>
        <div id="settings" class="sw-tab-content">
            <div class="col-xs-12">
                <h4>
                Connected successfully
                </h4>
                <p>
                Your api key is: {$apiKey}
                </p>
            </div>
            <div class="col-xs-12">
                <a href="{$disconnectUrl}" class="btn" style="background-color: tomato; color: #fff;">{l s='Disconnect'}</a>
            </div>
        </div>
      
    </div>
</div>



