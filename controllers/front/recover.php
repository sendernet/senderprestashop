<?php

/**
 *
 */
class SenderPrestashopRecoverModuleFrontController extends ModuleFrontController
{
    /**
     * Handle cart recover
     *
     * @return void
     */
    public function display()
    {
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')
            || !Tools::getIsset('hash')) {
            Tools::redirect($this->context->link->getPageLink('index'));
            $this->module->logDebug('Recover validation failed.');
            return;
        }

        // Here we retrieve the cart from Sender
        $cart = $this->module->apiClient()->cartGet(Tools::getValue('hash', 'NULL'));

        $this->module->logDebug('Cart get by hash: ' . Tools::getValue('hash', 'NULL'));
        $this->module->logDebug('Cart data: ' . Tools::jsonEncode($cart));

        if (!isset($cart->cart_id)) {
            Tools::redirect($this->context->link->getPageLink('index'));
            return;
        }

        // Assign cart for the user
        $this->context->cart = new Cart($cart->cart_id);
        $this->context->cookie->id_cart = $cart->cart_id;

        // Redirect
        Tools::redirect($this->context->link->getPageLink('index'));
    }
}
