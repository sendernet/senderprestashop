<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../senderprestashop.php');

$senderprestashop = new SenderPrestashop();


if (Tools::getValue('token') !== Tools::getAdminToken($senderprestashop->name)) {
    die(Tools::jsonEncode(array( 'result' => false )));
} else {
    switch (Tools::getValue('action')) {
        case 'saveAllowGuestCartTracking':
            if (Configuration::updateValue(
                'SPM_ALLOW_GUEST_TRACK',
                !Configuration::get('SPM_ALLOW_GUEST_TRACK')
            )) {
                die(Tools::jsonEncode(array(
                    'result' => Configuration::get('SPM_ALLOW_GUEST_TRACK')
                )));
            }
            die(Tools::jsonEncode(array( 'result' => false )));
            break;
        case 'saveCustomerListId':
            if (Configuration::updateValue(
                'SPM_ALLOW_GUEST_TRACK',
                Tools::getValue('list_id')
            )) {
                die(Tools::jsonEncode(array( 'result' => true)));
            }
            die(Tools::jsonEncode(array( 'result' => false )));
            break;
        case 'saveGuestListId':
            if (Configuration::updateValue(
                'SPM_GUEST_LIST_ID',
                Tools::getValue('list_id')
            )) {
                die(Tools::jsonEncode(array( 'result' => true)));
            }
            die(Tools::jsonEncode(array( 'result' => false )));
            break;
        default:
            exit;
    }
}
exit;
