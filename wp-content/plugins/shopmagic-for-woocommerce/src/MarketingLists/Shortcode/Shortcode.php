<?php
declare( strict_types=1 );

namespace WPDesk\ShopMagic\MarketingLists\Shortcode;

class Shortcode {

	/** @var int */
	public $list_id;

	/** @var bool */
	public $double_opt_in = false;

	/** @var bool */
	public $show_name = true;

	/** @var bool */
	public $show_labels = true;

	/** @var string */
	public $agreement = '';

	public function __construct( array $parameters ) {
		if ( empty( $parameters['id'] ) ) {
			throw new \InvalidArgumentException( 'Shortcode must reference list id.' );
		}
		$this->list_id = absint( $parameters['id'] );

		if ( in_array( 'name', $parameters, true ) ) {
			$this->show_name = true;
		} elseif ( isset( $parameters['name'] ) && $parameters['name'] == 'false' ) {
			$this->show_name = false;
		}

		if ( in_array( 'labels', $parameters, true ) ) {
			$this->show_labels = true;
		} elseif ( isset( $parameters['labels'] ) && $parameters['labels'] == 'false' ) {
			$this->show_labels = false;
		}

		if ( in_array( 'doubleOptin', $parameters, true ) ) {
			$this->double_opt_in = true;
		} elseif ( isset( $parameters['doubleOptin'] ) && $parameters['doubleOptin'] == 'false' ) {
			$this->double_opt_in = false;
		}

		$this->agreement = $parameters['agreement'] ?? '';
	}

}
