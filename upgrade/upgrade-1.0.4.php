<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_4($module)
{
    $hook_to_remove_id = Hook::getIdByName('actionValidateOrder');

    if ($hook_to_remove_id) {
        $module->unregisterHook((int)$hook_to_remove_id);
    }

    return $module->registerHook('displayOrderConfirmation');
}
