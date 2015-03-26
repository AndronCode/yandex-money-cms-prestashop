<?php

class yamoduleredirectkModuleFrontController extends ModuleFrontController
{
	public $display_header = false;
	public $display_column_left = false;
	public $display_column_right = false;
	public $display_footer = false;
	public $ssl = true;

	public function initContent()
	{
		$cart = $this->context->cart;

		$payments = array();
		$payments['message'] = $this->module->l('Заказ в статусе не оплачен! Перейдите в личный кабинет и нажмите перезаказ');
		if ($cart)
		{
			$total_to_pay = $cart->getOrderTotal(true);
			$rub_currency_id = Currency::getIdByIsoCode('RUB');
			if($cart->id_currency != $rub_currency_id)
			{
				$from_currency = new Currency($cart->id_curre1ncy);
				$to_currency = new Currency($rub_currency_id);
				$total_to_pay = Tools::convertPriceFull($total_to_pay, $from_currency, $to_currency);
			}

			$display = '';
			if (Configuration::get('YA_P2P_ACTIVE'))
			{
				$vars_p2p = Configuration::getMultiple(array(
					'YA_P2P_NUMBER',
					'YA_P2P_ACTIVE',
				));
				$this->context->smarty->assign(array(
					'DATA_P2P' => $vars_p2p,
					'price' => number_format($total_to_pay, 2, '.', ''),
					'cart' => $this->context->cart
				));

				$display .= $this->display(__FILE__, 'payment.tpl');
			}

			if (Configuration::get('YA_ORG_ACTIVE'))
			{
				$vars_org = Configuration::getMultiple(array(
					'YA_ORG_SHOPID',
					'YA_ORG_SCID',
					'YA_ORG_ACTIVE',
					'YA_ORG_TYPE',
				));

				$this->context->smarty->assign(array(
					'DATA_ORG' => $vars_org,
					'id_cart' => $cart->id,
					'customer' => new Customer($cart->id_customer),
					'address' => new Address($this->context->cart->id_address_delivery),
					'total_to_pay' => number_format($total_to_pay, 2, '.', ''),
					'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
					'shop_name' => Configuration::get('PS_SHOP_NAME')
				));

				$payments = Configuration::getMultiple(array(
					'YA_ORG_PAYMENT_YANDEX',
					'YA_ORG_PAYMENT_CARD',
					'YA_ORG_PAYMENT_MOBILE',
					'YA_ORG_PAYMENT_WEBMONEY',
					'YA_ORG_PAYMENT_TERMINAL',
					'YA_ORG_PAYMENT_SBER',
					'YA_ORG_PAYMENT_PB',
					'YA_ORG_PAYMENT_MA',
					'YA_ORG_PAYMENT_ALFA'
				));
				
				$payments['pt'] = Tools::getValue('type');
			}

			$this->module->validateOrder((int)$cart->id, _PS_OS_PREPARATION_, $cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, NULL, array(), NULL, false, $cart->secure_key);	

		}

		$this->context->smarty->assign($payments);

		return $this->setTemplate('redirectk.tpl');
	}
}