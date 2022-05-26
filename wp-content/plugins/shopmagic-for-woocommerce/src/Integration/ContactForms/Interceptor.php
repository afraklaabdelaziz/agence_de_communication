<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Integration\ContactForms;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use TypeError;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Customer\CustomerProvider;
use WPDesk\ShopMagic\Exception\CannotProvideCustomerException;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\Exception\FieldNotFound;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Guest\GuestFactory;

abstract class Interceptor implements CustomerProvider, Hookable {

	/** @var Customer */
	protected $customer;

	/** @var FormEntry|object */
	protected $form;

	/** @var CustomerDAO */
	protected $customer_dao;

	/** @var CustomerFactory */
	protected $customer_factory;

	/** @var GuestDAO */
	protected $guest_dao;

	/** @var GuestFactory */
	protected $guest_factory;

	final public function __construct( CustomerDAO $customer_dao, GuestDAO $guest_dao, GuestFactory $guest_factory, CustomerFactory $customer_factory ) {
		$this->customer_dao     = $customer_dao;
		$this->customer_factory = $customer_factory;
		$this->guest_dao        = $guest_dao;
		$this->guest_factory    = $guest_factory;
	}

	final public function get_customer(): Customer {
		if ( ! $this->form instanceof FormEntry && method_exists( $this->form, 'get_entry_email' ) ) {
			$email = $this->form->get_entry_email();
		} elseif ( ! $this->form instanceof FormEntry ) {
			throw new TypeError(
				sprintf(
					'%s::$form needs to be the type of %s. %s given',
					__CLASS__,
					FormEntry::class,
					is_object( $this->form ) ? get_class( $this->form ) : gettype( $this->form )
				)
			);
		} else {
			try {
				$email = $this->form->get_email();
			} catch ( FieldNotFound $e ) {
				throw new CannotProvideCustomerException();
			}
		}

		try {
			return $this->customer_dao->find_by_email( $email );
		} catch ( CustomerNotFound $e ) {
			$guest = $this->guest_factory->create_from_email_and_db( $email );
			if ( ! $guest->is_saved() ) {
				$this->guest_dao->save( $guest );
			}
			return $this->customer_factory->create_from_guest( $guest );
		}
	}

	final public function is_customer_provided(): bool {
		try {
			return $this->get_customer() instanceof Customer;
		} catch ( \Throwable $e ) {
			return false;
		}
	}
}
