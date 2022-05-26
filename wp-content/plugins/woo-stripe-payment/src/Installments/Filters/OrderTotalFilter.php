<?php

namespace PaymentPlugins\Stripe\Installments\Filters;

class OrderTotalFilter extends AbstractFilter {

	private $total;

	public function __construct( $total ) {
		$this->total = $total;
	}

	function is_available() {
		return $this->total >= 300;
	}

}