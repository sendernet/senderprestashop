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

    <div class="col-sm-2 col-xs-12 sender-prestashop-hide-small sender-prestashop-menu">
        <ul class="spm-tabs spm-main-menu">
            <li class="tab-link spm-current spm-active" data-tab="spm-home">
                <a href="#!spm-home">
                    <i class="zmdi zmdi-home"></i>
                    {l s='Home' mod='senderprestashop'}
                </a>
            </li>
            <li class="tab-link" data-tab="spm-forms">
                <a href="#!spm-forms">
                    <i class="zmdi zmdi-format-list-bulleted"></i>
                    {l s='Forms' mod='senderprestashop'}
                </a>
            </li>
            <li class="tab-link" data-tab="spm-carts">
                <a href="#!spm-carts">
                    <i class="zmdi zmdi-shopping-cart"></i>
                    {l s='Cart tracking' mod='senderprestashop'}
                </a>
            </li>
            <li class="tab-link" data-tab="spm-push" disabled>
                <a href="#!spm-spm-push">
                    <i class="zmdi zmdi-notifications-active"></i>
                    {l s='Push notifications' mod='senderprestashop'}
                </a>
            </li>
           
        </ul>
    </div>

    <div class="col-sm-10 col-xs-12 sender-prestashop-content">
        {* HOME TAB *}
        <div id="spm-home" class="spm-tab-content spm-current">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i> 
                    {l s='Plugin status is' mod='senderprestashop'} 
                    <span style="color:green;">{l s='ACTIVE' mod='senderprestashop'}</span>
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <table class="table" style="margin-bottom: 25px;">
                            <tr>
                                <td>
                                    {l s='User:' mod='senderprestashop'} 
                                </td>
                                <td>
                                    <strong>{$connectedUser->email|escape:'htmlall':'UTF-8'}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {l s='Api key:' mod='senderprestashop'} 
                                </td>
                                <td>
                                    <strong>{$connectedUser->api_key|escape:'htmlall':'UTF-8'}</strong>
                                </td>
                            </tr>
                        </table>
                        <a href="{$disconnectUrl|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-danger">
                            {l s='Disconnect' mod='senderprestashop'}
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
                    {l s='Form widget information' mod='senderprestashop'}
                </div>
                <div class="panel-body">
                    <div class="alert alert-warning">
                        {l s='There was no form found on your Sender.net`s account. Please create a new form and refresh this page' mod='senderprestashop'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/forms/add">
                            {l s='Create a form' mod='senderprestashop'}
                        </a>
                    </p>
                </div>
            </div>
            {else}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-format-list-bulleted"></i> 
                    {l s='Widget is ' mod='senderprestashop'}
                    {if not $allowForms}
                        <span id="swToggleWidgetTitle" style="color:red;">
                            {l s='disabled' mod='senderprestashop'}
                        </span>
                    {else}
                        <span id="swToggleWidgetTitle" style="color:green;">
                            {l s='enabled' mod='senderprestashop'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <button id="swToggleWidget" class="btn btn-lg {if not $allowForms}btn-success{else}btn-danger{/if}">
                        {if not $allowForms}
                            {l s='Enable' mod='senderprestashop'}
                        {else}
                            {l s='Disable' mod='senderprestashop'}
                        {/if}
                        </button>
                    </div>
                    <blockquote>
                        <p>
                            {l s='When enabled, a Sender.net form widget will appear in the customization menu.
                             It allows you to insert your Sender.net form into anywhere on your web page.' mod='senderprestashop'}
                        </p>
                        <p>
                            <a href="#">
                                {l s='Manage widgets' mod='senderprestashop'}
                            </a>
                        </p>
                    </blockquote>
                    <div class="col-xs-12{if not $allowForms} hidden{/if}" id="forms_tab">
                        <div class="form-group">
                            <label for="swFormsSelect">
                                {l s='Select form' mod='senderprestashop'}
                            </label>
                            <select id="swFormsSelect" name="swFormsSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                <option value="0">
                                    {l s='Select a form' mod='senderprestashop'}
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
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i> 
                    {l s='Push notifications are' mod='senderprestashop'} 
                    {if not $allowPush}
                        <span id="swTogglePushTitle" style="color:red;">
                            {l s='disabled' mod='senderprestashop'}
                        </span>
                    {else}
                        <span id="swTogglePushTitle" style="color:green;">
                            {l s='enabled' mod='senderprestashop'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <button id="swTogglePush" class="btn btn-lg {if not $allowPush}btn-success{else}btn-danger{/if}">
                        {if not $allowPush}
                            {l s='Enable' mod='senderprestashop'}
                        {else}
                            {l s='Disable' mod='senderprestashop'}
                        {/if}
                        </button>
                    </div>
                    <blockquote class="{if $allowPush}hidden{/if}" id="push_disabled">
                        <p>
                            {l s='When enabled, this feature shows your push project’s subscribe icon on your website. You can manage the push campaigns in your Sender.net account’s dashboard.' mod='senderprestashop'}
                        </p>
                        <p>
                            <a target="_BLANK" href="http://help.sender.net/section/push-notifications/">
                                {l s='Getting started with push notifications' mod='senderprestashop'}
                            </a>
                        </p>
                    </blockquote>
                    <blockquote class="{if not $allowPush}hidden{/if}" id="push_enabled">
                        {if not $pushProject}
                            <h3>
                                <i class="zmdi zmdi-alert-circle-o"></i> 
                                {l s='You don’t have a push project configured' mod='senderprestashop'}
                            </h3>
                            <a class="sender-prestashop-button" target="_BLANK" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_projects/create">
                                {l s='Create a new push project' mod='senderprestashop'}
                            </a> 
                        {else}
                            <p>
                                {l s='When enabled, this feature shows your push project’s subscribe icon on your website. You can manage the push campaigns in your Sender.net account’s dashboard.' mod='senderprestashop'}
                            </p>
                            <p>
                                <a target="_BLANK" href="http://help.sender.net/section/push-notifications/">
                                    {l s='Getting started with push notifications' mod='senderprestashop'}
                                </a> | 
                                <a target="_BLANK" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_campaigns">
                                    {l s='Manage your push campaigns' mod='senderprestashop'}
                                </a> | 
                                <a target="_BLANK" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_projects/view">
                                    {l s='Customize push project' mod='senderprestashop'}
                                </a>
                            </p>
                        {/if}
                    </blockquote>
                </div>
            </div>
        </div>

        {* CART TRACKING Tab *}
        <div id="spm-carts" class="spm-tab-content">

            {* ALLOW CART TRACK *}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> 
                    {l s='Customer cart tracking is' mod='senderprestashop'}
                    {if not $allowCartTrack}
                        <span id="swToggleCartTrackTitle" style="color:red;">
                            {l s='disabled' mod='senderprestashop'}
                        </span>
                    {else}
                        <span id="swToggleCartTrackTitle" style="color:green;">
                            {l s='enabled' mod='senderprestashop'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    
                    <div class="spm-details-settings">
                        <button id="swToggleCartTrack" class="btn btn-lg {if not $allowCartTrack}btn-success{else}btn-danger{/if}">
                        {if not $allowCartTrack}
                            {l s='Enable' mod='senderprestashop'}
                        {else}
                            {l s='Disable' mod='senderprestashop'}
                        {/if}
                        </button>
                    </div>
                    <blockquote>
                        <p>
                            {l s='If this feature is enabled - logged in customers cart will be tracked.' mod='senderprestashop'}
                        </p>
                    </blockquote>
                </div>
            </div>

            {* SELECT CUSTOMERS LIST *}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> 
                    {l s='Customer list' mod='senderprestashop'}
                </div>
                <div class="panel-body">
                    {if empty($customersLists)}
                    <div class="alert alert-warning">
                        {l s='To track customers carts you must have at least one list at your Sender.net`s account' mod='senderprestashop'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                            {l s='Create a new list' mod='senderprestashop'}
                        </a>
                    </p>
                    {else}
                    <blockquote>
                            <p>
                                {l s='Select to which list save new signups and customers whose carts were tracked' mod='senderprestashop'}
                            </p>
                            <p>
                                <a href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists">
                                    {l s='Manage lists' mod='senderprestashop'}
                                </a>
                            </p>
                        </blockquote>
                        <div id="swCustomerListSelectContainer" class="form-group">
                            <label for="swCustomerListSelect">
                                {l s='Select list' mod='senderprestashop'}
                            </label>
                            <select id="swCustomerListSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                <option value="0">
                                    {l s='Select a list' mod='senderprestashop'}
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

            {* NEW SIGNUP PANEL *}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> 
                    {l s='Track new signups is:' mod='senderprestashop'}
                    {if not $allowNewSignups}
                        <span id="swToggleNewSignupsTitle" style="color:red;">
                            {l s='disabled' mod='senderprestashop'}
                        </span>
                    {else}
                        <span id="swToggleNewSignupsTitle" style="color:green;">
                            {l s='enabled' mod='senderprestashop'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    {if empty($customersLists)}
                    <div class="alert alert-warning">
                        {l s='To track new signups you must have at least one list at your Sender.net`s account' mod='senderprestashop'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                            {l s='Create a new list' mod='senderprestashop'}
                        </a>
                    </p>
                    {else}
                    <div class="spm-details-settings">
                        <button id="swToggleNewSignups" class="btn btn-lg {if not $allowNewSignups}btn-success{else}btn-danger{/if}">
                        {if not $allowNewSignups}
                            {l s='Enable' mod='senderprestashop'}
                        {else}
                            {l s='Disable' mod='senderprestashop'}
                        {/if}
                        </button>
                    </div>
                    <blockquote>
                        <p>
                            {l s='If this feature is enabled - all new customers who signs up will be added to the selected customer list.' mod='senderprestashop'}
                        </p>
                        <p>
                            <a href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists">
                                {l s='Manage lists' mod='senderprestashop'}
                            </a>
                        </p>
                    </blockquote>
                    {/if}
                </div>
            </div>

            {* ALLOW GUEST TRACKING PANEL *}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> 
                    {l s='Guest cart tracking is' mod='senderprestashop'} 
                    {if not $allowGuestCartTracking}
                        <span id="swToggleGuestCartTrackingTitle" style="color:red;">
                            {l s='disabled' mod='senderprestashop'}
                        </span>
                    {else}
                        <span id="swToggleGuestCartTrackingTitle" style="color:green;">
                            {l s='enabled' mod='senderprestashop'}
                        </span>
                    {/if}
                </div>
                <div class="panel-body">
                    {if empty($guestsLists)}
                    <div class="alert alert-warning">
                        {l s='To track guest user carts you must have at least one list at your Sender.net`s account' mod='senderprestashop'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                            {l s='Create a new list' mod='senderprestashop'}
                        </a>
                    </p>
                    {else}
                    <div class="spm-details-settings">
                        <button id="swToggleGuestCartTracking" class="btn btn-lg {if not $allowGuestCartTracking}btn-success{else}btn-danger{/if}">
                        {if not $allowGuestCartTracking}
                            {l s='Enable' mod='senderprestashop'}
                        {else}
                            {l s='Disable' mod='senderprestashop'}
                        {/if}
                        </button>
                    </div>
                    <blockquote>
                        <p>
                            {l s='When enabled, will track guest carts and save guest details to the list selected below.' mod='senderprestashop'}
                        </p>
                        <p>
                            <a href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists">
                                {l s='Manage lists' mod='senderprestashop'}
                            </a>
                        </p>
                    </blockquote>
                    <div class="col-xs-12{if not $allowGuestCartTracking} hidden{/if}" id="guests_lists">
                        <div class="form-group">
                            <label for="swGuestListSelect">
                                {l s='Select list' mod='senderprestashop'}
                            </label>
                            <select id="swGuestListSelect" name="swGuestListSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                <option value="0">
                                    {l s='Select a list' mod='senderprestashop'}
                                </option>
                                {foreach $guestsLists as $guestsList}
                                <option {if $guestsList->id eq $guestListId}selected="selected"{/if} value="{$guestsList->id|escape:'htmlall':'UTF-8'}">
                                    {$guestsList->title|escape:'htmlall':'UTF-8'}
                                </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    {/if}
                </div>
            </div>

        </div>
    </div>
</div>