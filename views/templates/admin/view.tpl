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
var cartsAjaxurl = '{$cartsAjaxurl|escape:'htmlall':'UTF-8'}';
var formsAjaxurl = '{$formsAjaxurl|escape:'htmlall':'UTF-8'}';
var listsAjaxurl = '{$listsAjaxurl|escape:'htmlall':'UTF-8'}';
var pushAjaxurl = '{$pushAjaxurl|escape:'htmlall':'UTF-8'}';
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
            <li class="tab-link" data-tab="spm-push" disabled>
                <a href="#!spm-spm-push"><i class="zmdi zmdi-notifications-active"></i> Push notifications</a>
            </li>
            <li class="tab-link" data-tab="spm-forms">
                <a href="#!spm-forms"><i class="zmdi zmdi-format-list-bulleted"></i> Forms</a>
            </li>
            <li class="tab-link spm-current spm-active" data-tab="spm-settings">
                <a href="#!spm-settings"><i class="zmdi zmdi-settings"></i> Settings</a>
            </li>
        </ul>
    </div>
    <div class="col-sm-10 col-xs-12 sender-prestashop-content">
        <div id="spm-forms" class="spm-tab-content">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-format-list-bulleted"></i> Widget is {if not $allowForms}<span id="swToggleWidgetTitle" style="color:red;">disabled</span>{else}<span id="swToggleWidgetTitle" style="color:green;">enabled</span>{/if}
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <button id="swToggleWidget" class="btn btn-lg {if not $allowForms}btn-success{else}btn-danger{/if}">{if not $allowForms}Enable{else}Disable{/if}</button>
                    </div>
                    <blockquote>
                        <p>
                            When enabled, a Sender.net form widget will appear in the customization menu. It allows you to insert your Sender.net form into anywhere on your web page.
                        </p>
                        <p>
                            <a href="#">Manage widgets</a>
                        </p>
                    </blockquote>
                    <div class="col-xs-12{if not $allowForms} hidden{/if}" id="forms_tab">
                        <div class="form-group">
                            <label for="swFormsSelect">Select form</label>
                            <select id="swFormsSelect" name="swFormsSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                {foreach $formsList as $form}
                                <option {if $form->id eq $formId}selected="selected"{/if} value="{$form->id|escape:'htmlall':'UTF-8'}">{$form->title|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="spm-push" class="spm-tab-content">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i> Push notifications are {if not $allowPush}<span id="swTogglePushTitle" style="color:red;">disabled</span>{else}<span id="swTogglePushTitle" style="color:green;">enabled</span>{/if}
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <button id="swTogglePush" class="btn btn-lg {if not $allowPush}btn-success{else}btn-danger{/if}">{if not $allowPush}Enable{else}Disable{/if}</button>
                    </div>
                    <blockquote class="{if $allowPush}hidden{/if}" id="push_disabled">
                        <p>
                            When enabled, this feature shows your push project's subscribe icon on your website. You can manage the push campaigns in your Sender.net account’s dashboard.
                        </p>
                        <p>
                            <a target="_BLANK" href="http://help.sender.net/section/push-notifications/">Getting started with push notifications</a>
                        </p>
                    </blockquote>
                    <blockquote class="{if not $allowPush}hidden{/if}" id="push_enabled">
                        {if not $pushProject}
                        <h3><i class="zmdi zmdi-alert-circle-o"></i> You don't have a push project configured</h3>
                        <a class="sender-prestashop-button" target="_BLANK" href="{$baseUrl}/push_projects/create">Create a new push project</a> {else}
                        <p>
                            When enabled, this feature shows your push project's subscribe icon on your website. You can manage the push campaigns in your Sender.net account’s dashboard.
                        </p>
                        <p>
                            <a target="_BLANK" href="http://help.sender.net/section/push-notifications/">Getting started with push notifications</a> | <a target="_BLANK" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_campaigns">Manage your push campaigns</a> | <a target="_BLANK" href="{$baseUrl|escape:'htmlall':'UTF-8'}/push_projects/view">Customize push project</a>
                        </p>
                        {/if}
                    </blockquote>
                </div>
            </div>
        </div>
        <div id="spm-settings" class="spm-tab-content spm-current">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i> Plugin status is <span style="color:green;">ACTIVE</span>
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <h4>
                        Connected successfully
                        </h4>
                        <p>
                            Your api key is: {$apiKey|escape:'htmlall':'UTF-8'}
                        </p>
                        <a href="{$disconnectUrl|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-danger">{l s='Disconnect' mod='senderprestashop'}</a>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> Customers list selection
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <div class="form-group">
                            <label for="swGuestListSelect">Select list</label>
                            <select id="swCustomerListSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                {foreach $customersLists as $customerList}
                                <option {if $customerList->id eq $customerListId}selected="selected"{/if} value="{$customerList->id|escape:'htmlall':'UTF-8'}">{$customerList->title|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <blockquote>
                        <p>
                            Please select a list to add your customers to
                        </p>
                        <p>
                            <a href="https://app.sender.net/mailinglists">Manage lists</a>
                        </p>
                    </blockquote>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-shopping-cart"></i> Guest cart tracking is {if not $allowGuestCartTracking}<span id="swToggleGuestCartTrackingTitle" style="color:red;">disabled</span>{else}<span id="swToggleGuestCartTrackingTitle" style="color:green;">enabled</span>{/if}
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <button id="swToggleGuestCartTracking" class="btn btn-lg {if not $allowGuestCartTracking}btn-success{else}btn-danger{/if}">{if not $allowGuestCartTracking}Enable{else}Disable{/if}</button>
                    </div>
                    <blockquote>
                        <p>
                            When enabled, will track guest carts and save guest details to the list selected below.
                        </p>
                        <p>
                            <a href="https://app.sender.net/mailinglists">Manage lists</a>
                        </p>
                    </blockquote>
                    <div class="col-xs-12{if not $allowGuestCartTracking} hidden{/if}" id="guests_lists">
                        <div class="form-group">
                            <label for="swGuestListSelect">Select list</label>
                            <select id="swGuestListSelect" name="swGuestListSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                {foreach $guestsLists as $guestsList}
                                <option {if $guestsList->id eq $guestListId}selected="selected"{/if} value="{$guestsList->id|escape:'htmlall':'UTF-8'}">{$guestsList->title|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>