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
        // Check if we can proceed
        if (!Configuration::get($this->_optionPrefix . 'module_active')
            || !Tools::getIsset('hash')
            || !Validate::isLoadedObject($this->context->cookie)) {
            Tools::redirect($this->context->link->getPageLink('index'));
            return;
        }

        // Here we retrieve the cart from Sender
        $cart = $this->module->apiClient->cartGet(Tools::getValue('hash', 'NULL'));

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
