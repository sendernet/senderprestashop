<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../senderprestashop.php');

$senderprestashop = new SenderPrestashop();


if (Tools::getValue('token') !== Tools::getAdminToken($senderprestashop->name)) {
    die( Tools::jsonEncode( array( 'result' => false )));
} else {
    switch (Tools::getValue('action')) {
        case 'saveAllowGuestCartTracking' :
            if (Configuration::updateValue($senderprestashop->_optionPrefix . '_allow_guest_cart_tracking',
                !Configuration::get($senderprestashop->_optionPrefix . '_allow_guest_cart_tracking'))) {
                die( Tools::jsonEncode( array(
                    'result' => Configuration::get($senderprestashop->_optionPrefix . '_allow_guest_cart_tracking')
                )));
            }
            die( Tools::jsonEncode( array( 'result' => false )));
            break;
        case 'saveCustomerListId' :
            if (Configuration::updateValue($senderprestashop->_optionPrefix . '_customer_list_id',
                Tools::getValue('list_id'))) {
                die( Tools::jsonEncode( array( 'result' => true)));
            }
            die( Tools::jsonEncode( array( 'result' => false )));
            break;
        case 'saveGuestListId' :
            if (Configuration::updateValue($senderprestashop->_optionPrefix . '_guest_list_id',
                Tools::getValue('list_id'))) {
                die( Tools::jsonEncode( array( 'result' => true)));
            }
            die( Tools::jsonEncode( array( 'result' => false )));
            break;
        default:
            exit;
    }
}
exit;