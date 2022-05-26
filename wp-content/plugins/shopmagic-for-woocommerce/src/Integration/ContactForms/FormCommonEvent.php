<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Integration\ContactForms;

use Throwable;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Customer\CustomerProvider;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Exception\CannotProvideCustomerException;
use WPDesk\ShopMagic\FormField\Field\SelectField;

abstract class FormCommonEvent extends \WPDesk\ShopMagic\Event\BasicEvent {
	const FIELD_ID_FORM = 'form';

	/** @var FormEntry */
	protected $form_data;

	/** @var CustomerProvider */
	protected $customer_provider;

	/** @var ?Customer */
	protected $customer;

	/** @var CustomerFactory */
	protected $customer_factory;

	public function __construct( CustomerProvider $customer_provider, CustomerFactory $customer_factory ) {
		$this->customer_provider = $customer_provider;
		$this->customer_factory  = $customer_factory;
	}

	final public function get_group_slug(): string {
		return EventFactory2::GROUP_FORMS;
	}

	/** @return string[] */
	public function get_provided_data_domains(): array {
		return array_merge(
			parent::get_provided_data_domains(),
			[
				FormEntry::class,
				Customer::class,
			]
		);
	}

	/** @return FormEntry[] */
	public function get_provided_data(): array {
		$data = [
			FormEntry::class => $this->form_data,
		];

		try {
			$data[ Customer::class ] = $this->get_customer();
		} catch ( Throwable $e ) {

		}

		return array_merge(
			parent::get_provided_data(),
			$data
		);
	}

	final protected function get_customer(): Customer {
		if ( $this->customer instanceof Customer ) {
			return $this->customer;
		}

		return $this->customer_provider->get_customer();
	}

	/** @return \ShopMagicVendor\WPDesk\Forms\Field[] */
	final public function get_fields(): array {
		return [
			( new SelectField() )
				->set_name( self::FIELD_ID_FORM )
				->set_label( __( 'Contact Form:', 'shopmagic-for-woocommerce' ) )
				->set_required()
				->set_options( $this->get_forms_as_options() ),
		];
	}

	/** @return string[] */
	abstract protected function get_forms_as_options(): array;

	/** @return array{form_data: FormEntry, customer_id?: string} */
	public function jsonSerialize(): array {
		$data = [ 'form_data' => $this->form_data ];

		try {
			$data['customer_id'] = $this->get_customer()->get_id();
		} catch ( CannotProvideCustomerException $e ) {
		}

		return $data;
	}

	public function set_from_json( array $serializedJson ) {
		if ( isset( $serializedJson['customer_id'] ) ) {
			$this->customer = $this->customer_factory->create_from_id( $serializedJson['customer_id'] );
		}
	}

}
