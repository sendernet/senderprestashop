<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../senderprestashop.php');

$senderprestashop = new SenderPrestashop();


if (Tools::getValue('token') !== Tools::getAdminToken($senderprestashop->name)) {
    die(Tools::jsonEncode(array( 'result' => false )));
} else {
    switch (Tools::getValue('action')) {
        case 'saveAllowPush':
            if (Configuration::updateValue(
                'SPM_ALLOW_PUSH',
                !Configuration::get('SPM_ALLOW_PUSH')
            )) {
                die(Tools::jsonEncode(array(
                    'result' => Configuration::get('SPM_ALLOW_PUSH')
                )));
            }
            die(Tools::jsonEncode(array( 'result' => false )));
            break;
        default:
            exit;
    }
}
exit;
