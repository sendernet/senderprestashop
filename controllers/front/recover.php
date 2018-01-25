<?php

// require_once 'lib/Sender/SenderApiClient.php';

class SenderPrestashopRecoverModuleFrontController extends ModuleFrontController
{
    public function display()
    {
        $cart = $this->module->apiClient->cartGet(Tools::getValue('hash', 'NULL'));

        $this->context->cart = new Cart($cart->cart_id);
        $this->context->cookie->id_cart = $cart->cart_id;
        // ppp($this->context);
        Tools::redirect($this->context->link->getPageLink('index'));
    }
}
