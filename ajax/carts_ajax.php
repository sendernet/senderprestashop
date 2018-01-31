<?php
/**
 * 2010-2018 Sender.net
 *
 * Sender.net Integration module for prestahop
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

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
            // break;
        case 'saveAllowCartTracking':
            if (Configuration::updateValue(
                'SPM_ALLOW_TRACK_CARTS',
                !Configuration::get('SPM_ALLOW_TRACK_CARTS')
            )) {
                die(Tools::jsonEncode(array(
                    'result' => Configuration::get('SPM_ALLOW_TRACK_CARTS')
                )));
            }
            die(Tools::jsonEncode(array( 'result' => false )));
        default:
            exit;
    }
}
exit;