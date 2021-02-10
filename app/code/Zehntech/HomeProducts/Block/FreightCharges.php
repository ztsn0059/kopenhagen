<?php

namespace Zehntech\HomeProducts\Block;

/**
 * @api
 * @since 100.0.2
 */
class FreightCharges extends \Magento\Framework\View\Element\Template
{
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Checkout\Model\Cart $cart
	) {
		parent::__construct($context);
		$this->cart = $cart;
	}

	public function getCartTotal() {
		return $this->cart->getQuote()->getSubtotal();
	}
}