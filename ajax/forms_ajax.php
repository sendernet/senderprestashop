<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../senderprestashop.php');

$senderprestashop = new SenderPrestashop();


if (Tools::getValue('token') !== Tools::getToken(false)) {
    die( Tools::jsonEncode( array( 'result' => false )));
} else {
    switch (Tools::getValue('action')) {
        case 'saveAllowForms' :
            if (Configuration::updateValue($senderprestashop->_optionPrefix . '_allow_forms',
                !Configuration::get($senderprestashop->module->_optionPrefix . '_allow_forms'))) {
                die( Tools::jsonEncode( array(
                    'result' => Configuration::get($senderprestashop->module->_optionPrefix . '_allow_forms')
                )));
            }
            die( Tools::jsonEncode( array( 'result' => false )));
            break;
        case 'saveFormId' :
            if (Configuration::updateValue($senderprestashop->_optionPrefix . '_form_id',
                Tools::getValue('form_id'))) {
                die( Tools::jsonEncode( array( 'result' => true)));
            }
            die( Tools::jsonEncode( array( 'result' => false )));
            break;
        default:
            exit;
    }
}
exit;