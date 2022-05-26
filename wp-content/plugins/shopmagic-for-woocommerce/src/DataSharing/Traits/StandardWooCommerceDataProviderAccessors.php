<?php

namespace WPDesk\ShopMagic\DataSharing\Traits;

use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Integration\ContactForms\FormEntry;

trait StandardWooCommerceDataProviderAccessors {
	/**
	 * @return \WP_User
	 */
	protected function get_user() {
		return $this->provided_data[ \WP_User::class ];
	}

	protected function is_automation_provieded(): bool {
		return isset( $this->provided_data[ Automation::class ] ) && $this->provided_data[ Automation::class ] instanceof Automation;
	}

	protected function get_automation(): Automation {
		return $this->provided_data[ Automation::class ];
	}

	/**
	 * @return Customer
	 */
	protected function get_customer() {
		return $this->provided_data[ Customer::class ];
	}

	/**
	 * @return bool
	 */
	protected function is_user_guest() {
		if ( $this->is_user_provided() ) {
			$user = $this->get_user();
			return ! isset( $user->ID );
		}

		return $this->get_customer()->is_guest();
	}

	protected function is_user_provided(): bool {
		return isset( $this->provided_data[ \WP_User::class ] ) && $this->provided_data[ \WP_User::class ] instanceof \WP_User;
	}

	protected function is_customer_provided(): bool {
		return isset( $this->provided_data[ Customer::class ] ) && $this->provided_data[ Customer::class ] instanceof Customer;
	}

	/**
	 * @return \WC_Order $order Can be \WC_Order or \WC_Order_Refund
	 */
	protected function get_order() {
		return $this->provided_data[ \WC_Order::class ];
	}

	protected function is_subscription_provided(): bool {
		return isset( $this->provided_data[ \WC_Subscription::class ] ) && $this->provided_data[ \WC_Subscription::class ] instanceof \WC_Subscription;
	}

	protected function get_subscription(): \WC_Subscription {
		return $this->provided_data[ \WC_Subscription::class ];
	}

	protected function parent_order_exists(): bool {
		if ( $this->is_subscription_provided() ) {
			$order = $this->get_subscription();
		} else {
			$order = $this->get_order();
		}

		return $order->get_parent_id() > 0;
	}

	protected function get_parent_order(): \WC_Abstract_Order {
		if ( $this->is_subscription_provided() ) {
			$order = $this->get_subscription();
		} else {
			$order = $this->get_order();
		}

		return wc_get_order( $order->get_parent_id() );
	}

	protected function is_order_provided(): bool {
		return isset( $this->provided_data[ \WC_Order::class ] ) && $this->provided_data[ \WC_Order::class ] instanceof \WC_Order;
	}

	protected function is_abstract_order_provided(): bool {
		return isset( $this->provided_data[ \WC_Order::class ] ) && $this->provided_data[ \WC_Order::class ] instanceof \WC_Abstract_Order;
	}

	/**
	 * @return \WP_Comment
	 */
	protected function get_comment() {
		return $this->provided_data[ \WP_Comment::class ];
	}

	/**
	 * @return bool
	 */
	protected function is_comment_provided() {
		return isset( $this->provided_data[ \WP_Comment::class ] ) && $this->provided_data[ \WP_Comment::class ] instanceof \WP_Comment;
	}

	/**
	 * @return \WC_Product $product
	 */
	protected function get_product() {
		return $this->provided_data[ \WC_Product::class ];
	}

	/**
	 * @return bool
	 */
	protected function is_product_provided() {
		return isset( $this->provided_data[ \WC_Product::class ] ) && $this->provided_data[ \WC_Product::class ] instanceof \WC_Product;
	}

	protected function get_membership(): \WC_Memberships_User_Membership {
		return $this->provided_data[ \WC_Memberships_User_Membership::class ];
	}

	protected function is_form_provided(): bool {
		return isset( $this->provided_data[ FormEntry::class ] ) && $this->provided_data[ FormEntry::class ] instanceof FormEntry;
	}

	protected function get_form(): FormEntry {
		return $this->provided_data[ FormEntry::class ];
	}
}
