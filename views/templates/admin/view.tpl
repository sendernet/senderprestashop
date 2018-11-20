{*
 * 2010-2018 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 *}
<script>
var cartsAjaxurl = "{$cartsAjaxurl|escape:'htmlall':'UTF-8'}";
var formsAjaxurl = "{$formsAjaxurl|escape:'htmlall':'UTF-8'}";
var listsAjaxurl = "{$listsAjaxurl|escape:'htmlall':'UTF-8'}";
var pushAjaxurl = "{$pushAjaxurl|escape:'htmlall':'UTF-8'}";
</script>
<div class="sender-prestashop-card">
    <div class="sender-prestashop-header">
        <div class="spm-text-left">
            <img src="{$imageUrl|escape:'htmlall':'UTF-8'}" alt="Sender Logo">
            <span>
                <small style="vertical-align: bottom;">v{$moduleVersion|escape:'htmlall':'UTF-8'}</small>
            </span>
        </div>
    </div>
    <div class="panel panel-default col-sm-2 col-xs-12" style="margin-top: 15px;">
        <div class="panel-heading">
            <i class="zmdi zmdi-notifications-active"></i> 
            {l s='Menu' mod='senderautomatedemails'}    
        </div>
        <div class="panel-body" style="padding: 0px;">
            <div class="">
                <ul class="spm-tabs spm-main-menu">
                    <li class="tab-link spm-current spm-active" data-tab="spm-home">
                        <a href="#!spm-home">
                            <i class="zmdi zmdi-home"></i>
                            {l s='Home' mod='senderautomatedemails'}
                        </a>
                    </li>
                    <li class="tab-link" data-tab="spm-forms">
                        <a href="#!spm-forms">
                            <i class="zmdi zmdi-format-list-bulleted"></i>
                            {l s='Forms' mod='senderautomatedemails'}
                        </a>
                    </li>
                    <li class="tab-link" data-tab="spm-carts">
                        <a href="#!spm-carts">
                            <i class="zmdi zmdi-shopping-cart"></i>
                            {l s='Cart tracking' mod='senderautomatedemails'}
                        </a>
                    </li>
                    <li class="tab-link" data-tab="spm-push" disabled>
                        <a href="#!spm-spm-push">
                            <i class="zmdi zmdi-notifications-active"></i>
                            {l s='Push notifications' mod='senderautomatedemails'}
                        </a>
                    </li>
                   
                </ul>
            </div>
        </div>
    </div>
    


    <div class="col-sm-10 col-xs-12 sender-prestashop-content">
        {* HOME TAB *}
        <div id="spm-home" class="spm-tab-content spm-current">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i> 
                    {l s='Plugin status is' mod='senderautomatedemails'} 
                    <span style="color:green;">{l s='ACTIVE' mod='senderautomatedemails'}</span>
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <table class="table" style="margin-bottom: 25px;">
                            <tr>
                                <td>
                                    {l s='User:' mod='senderautomatedemails'} 
                                </td>
                                <td>
                                    <strong>{$connectedUser->email|escape:'htmlall':'UTF-8'}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {l s='Api key:' mod='senderautomatedemails'} 
                                </td>
                                <td>
                                    <strong>{$connectedUser->api_key|escape:'htmlall':'UTF-8'}</strong>
                                </td>
                            </tr>
                        </table>
                        <a href="{$disconnectUrl|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-danger">
                            {l s='Disconnect' mod='senderautomatedemails'}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {* FORM Settings tab *}
        <div id="spm-forms" class="spm-tab-content">
            {if isset($formsList->error)}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-format-list-bulleted"></i> 
                    {l s='Form widget information' mod='senderautomatedemails'}
                </div>
                <div class="panel-body">
                    <div class="alert alert-warning">
                        {l s='There was no form found on your Sender.net`s account. Please create a new form and refresh this page' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/forms/add">
                            {l s='Create a form' mod='senderautomatedemails'}
                        </a>
                    </p>
                </div>
            </div>
            {else}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-format-list-bulleted"></i> 
                    {l s='Widget is ' mod='senderautomatedemails'}
                    {if not $allowForms}
                        <span id="swToggleWidgetTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
                    {else}
                        <span id="swToggleWidgetTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <button id="swToggleWidget" class="btn btn-lg {if not $allowForms}btn-success{else}btn-danger{/if}">
                        {if not $allowForms}
                            {l s='Enable' mod='senderautomatedemails'}
                        {else}
                            {l s='Disable' mod='senderautomatedemails'}
                        {/if}
                        </button>
                    </div>
                    <blockquote>
                        <p>
                            {l s='When enabled, a Sender.net form widget will appear in the customization menu.
                             It allows you to insert your Sender.net form into anywhere on your web page.' mod='senderautomatedemails'}
                        </p>
                    </blockquote>
                    <div class="col-xs-12{if not $allowForms} hidden{/if}" id="forms_tab">
                        <div class="form-group">
                            <label for="swFormsSelect">
                                {l s='Select form' mod='senderautomatedemails'}
                            </label>
                            <select id="swFormsSelect" name="swFormsSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                <option value="0">
                                    {l s='Select a form' mod='senderautomatedemails'}
                                </option>
                                {foreach $formsList as $form}
                                <option {if $form->id eq $formId}selected="selected"{/if} value="{$form->id|escape:'htmlall':'UTF-8'}">
                                    {$form->title|escape:'htmlall':'UTF-8'}
                                </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            {/if}

        </div>

        {* PUSH Settings tab *}
        <div id="spm-push" class="spm-tab-content">
            {if not $pushProject}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-format-list-bulleted"></i> 
                    {l s='Push project' mod='senderautomatedemails'}
                </div>
                <div class="panel-body">
                    <div class="alert alert-warning">
                        {l s='There was no push project found on your Sender.net`s account. Please configure and refresh this page' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_projects/create">
                            {l s='Configure push project' mod='senderautomatedemails'}
                        </a>
                    </p>
                </div>
            </div>
            {else}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i> 
                    {l s='Push notifications are' mod='senderautomatedemails'} 
                    {if not $allowPush}
                        <span id="swTogglePushTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
                    {else}
                        <span id="swTogglePushTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <button id="swTogglePush" class="btn btn-lg {if not $allowPush}btn-success{else}btn-danger{/if}">
                        {if not $allowPush}
                            {l s='Enable' mod='senderautomatedemails'}
                        {else}
                            {l s='Disable' mod='senderautomatedemails'}
                        {/if}
                        </button>
                    </div>
                    <blockquote class="{if $allowPush}hidden{/if}" id="push_disabled">
                        <p>
                            {l s='When enabled, this feature shows your push project’s subscribe icon on your website. You can manage the push campaigns in your Sender.net account’s dashboard.' mod='senderautomatedemails'}
                        </p>
                        <p>
                            <a target="_BLANK" href="http://help.sender.net/section/push-notifications/">
                                {l s='Getting started with push notifications' mod='senderautomatedemails'}
                            </a>
                        </p>
                    </blockquote>
                    <blockquote class="{if not $allowPush}hidden{/if}" id="push_enabled">
                        <p>
                            {l s='When enabled, this feature shows your push project’s subscribe icon on your website. You can manage the push campaigns in your Sender.net account’s dashboard.' mod='senderautomatedemails'}
                        </p>
                        <p>
                            <a target="_BLANK" href="http://help.sender.net/section/push-notifications/">
                                {l s='Getting started with push notifications' mod='senderautomatedemails'}
                            </a> | 
                            <a target="_BLANK" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_campaigns">
                                {l s='Manage your push campaigns' mod='senderautomatedemails'}
                            </a> | 
                            <a target="_BLANK" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_projects/view">
                                {l s='Customize push project' mod='senderautomatedemails'}
                            </a>
                        </p>
                    </blockquote>
                </div>
            </div>
            {/if}
        </div>

        {* CART TRACKING Tab *}
        <div id="spm-carts" class="spm-tab-content">

            {* ALLOW CART TRACK *}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> 
                    {l s='Customer cart tracking is' mod='senderautomatedemails'}
                    {if not $allowCartTrack}
                        <span id="swToggleCartTrackTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
                    {else}
                        <span id="swToggleCartTrackTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    
                    <div class="spm-details-settings">
                        <button id="swToggleCartTrack" class="btn btn-lg {if not $allowCartTrack}btn-success{else}btn-danger{/if}">
                        {if not $allowCartTrack}
                            {l s='Enable' mod='senderautomatedemails'}
                        {else}
                            {l s='Disable' mod='senderautomatedemails'}
                        {/if}
                        </button>
                    </div>
                    <blockquote>
                        <p>
                            {l s='If this feature is enabled - logged in customers cart will be tracked.' mod='senderautomatedemails'}
                        </p>
                    </blockquote>
                </div>
            </div>

            {* SELECT CUSTOMERS LIST *}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> 
                    {l s='Customer list' mod='senderautomatedemails'}
                </div>
                <div class="panel-body">
                    {if empty($customersLists)}
                    <div class="alert alert-warning">
                        {l s='To track customers carts you must have at least one list at your Sender.net`s account' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                            {l s='Create a new list' mod='senderautomatedemails'}
                        </a>
                    </p>
                    {else}
                    <blockquote>
                            <p>
                                {l s='Select to which list save new signups and customers whose carts were tracked' mod='senderautomatedemails'}
                            </p>
                            <p>
                                <a href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists">
                                    {l s='Manage lists' mod='senderautomatedemails'}
                                </a>
                            </p>
                        </blockquote>
                        <div id="swCustomerListSelectContainer" class="form-group">
                            <label for="swCustomerListSelect">
                                {l s='Select list' mod='senderautomatedemails'}
                            </label>
                            <select id="swCustomerListSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                <option value="0">
                                    {l s='Select a list' mod='senderautomatedemails'}
                                </option>
                                {foreach $customersLists as $customerList}
                                <option {if $customerList->id eq $customerListId}selected="selected"{/if} value="{$customerList->id|escape:'htmlall':'UTF-8'}">
                                    {$customerList->title|escape:'htmlall':'UTF-8'}
                                </option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}
                </div>
            </div>
            
            {* ALLOW GUEST TRACKING PANEL *}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i>
                    {l s='Guest cart tracking is' mod='senderautomatedemails'}
                    {if not $allowGuestCartTracking}
                        <span id="swToggleGuestCartTrackingTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
                    {else}
                        <span id="swToggleGuestCartTrackingTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    {if empty($guestsLists)}
                    <div class="alert alert-warning">
                        {l s='To track guest user carts you must have at least one list at your Sender.net`s account' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                            {l s='Create a new list' mod='senderautomatedemails'}
                        </a>
                    </p>
                    {else}
                    <div class="spm-details-settings">
                        <button id="swToggleGuestCartTracking" class="btn btn-lg {if not $allowGuestCartTracking}btn-success{else}btn-danger{/if}">
                        {if not $allowGuestCartTracking}
                            {l s='Enable' mod='senderautomatedemails'}
                        {else}
                            {l s='Disable' mod='senderautomatedemails'}
                        {/if}
                        </button>
                    </div>
                    <blockquote>
                        <p>
                            {l s='When enabled, will track guest carts and save guest details to the list selected below.' mod='senderautomatedemails'}
                        </p>
                        <p>
                            <a href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists">
                                {l s='Manage lists' mod='senderautomatedemails'}
                            </a>
                        </p>
                    </blockquote>
                    {/if}
                </div>
            </div>

        </div>
    </div>
</div>