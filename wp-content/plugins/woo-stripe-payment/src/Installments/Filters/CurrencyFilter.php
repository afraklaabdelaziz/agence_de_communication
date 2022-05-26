<?php

namespace PaymentPlugins\Stripe\Installments\Filters;

class CurrencyFilter extends AbstractFilter {

	private $currency;

	/**
	 * @var string[]
	 */
	private $supported_currencies = [ 'MXN' ];

	/**
	 * @var string[]
	 */
	private $supported_countries = [ 'MX' ];

	/**
	 * @var string
	 */
	private $account_country;

	public function __construct( $currency, $account_country ) {
		$this->currency        = $currency;
		$this->account_country = $account_country;
	}

	public function is_available() {
		$is_available = false;
		if ( $this->account_country ) {
			$is_available = \in_array( $this->account_country, $this->supported_countries );
		}

		return $is_available && \in_array( $this->currency, $this->supported_currencies );
	}

	public function get_supported_countries() {
		return $this->supported_countries;
	}

}