<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../senderprestashop.php');

$senderprestashop = new SenderPrestashop();


if (Tools::getValue('token') !== Tools::getAdminToken($senderprestashop->name)) {
    die(Tools::jsonEncode(array( 'result' => false )));
} else {
    switch (Tools::getValue('action')) {
        case 'saveAllowForms':
            if (Configuration::updateValue(
                'SPM_ALLOW_FORMS',
                !Configuration::get('SPM_ALLOW_FORMS')
            )) {
                die(Tools::jsonEncode(array(
                    'result' => Configuration::get('SPM_ALLOW_FORMS')
                )));
            }
            die(Tools::jsonEncode(array( 'result' => false )));
            break;
        case 'saveFormId':
            if (Configuration::updateValue(
                'SPM_FORM_ID',
                Tools::getValue('form_id')
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
