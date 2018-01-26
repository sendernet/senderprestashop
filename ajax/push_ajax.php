<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../senderprestashop.php');

$senderprestashop = new SenderPrestashop();


if (Tools::getValue('token') !== Tools::getAdminToken($senderprestashop->name)) {
    die( Tools::jsonEncode( array( 'result' => false )));
} else {
    switch (Tools::getValue('action')) {
        case 'saveAllowPush' :
            if (Configuration::updateValue($senderprestashop->_optionPrefix . '_allow_push',
                !Configuration::get($senderprestashop->_optionPrefix . '_allow_push'))) {
                die( Tools::jsonEncode( array(
                    'result' => Configuration::get($senderprestashop->_optionPrefix . '_allow_push')
                )));
            }
            die( Tools::jsonEncode( array( 'result' => false )));
            break;
        default:
            exit;
    }
}
exit;