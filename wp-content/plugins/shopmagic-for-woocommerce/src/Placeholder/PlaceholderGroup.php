<?php

namespace WPDesk\ShopMagic\Placeholder;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\DataSharing\DataAccess\NewUserAccount;

final class PlaceholderGroup {
	const ORDER        = 'order';
	const PRODUCT      = 'product';
	const USER         = 'customer';
	const SHOP         = 'shop';
	const SUBSCRIPTION = 'subscription';
	const CART         = 'cart';
	const MEMBERSHIP   = 'membership';
	const FORM         = 'form';

	/**
	 * @param string|string[] $classes
	 *
	 * @return string
	 */
	public static function class_to_group( $classes ) {
		if ( ! is_array( $classes ) ) {
			$classes = [ $classes ];
		}
		foreach ( $classes as $class ) {
			// WC_Subscription has to be first as it extends Order.
			if ( is_a( $class, \WPDesk\ShopMagicCart\Cart\Cart::class, true ) ) {
				return self::CART;
			}
			if ( is_a( $class, \WC_Subscription::class, true ) ) {
				return self::SUBSCRIPTION;
			}
			if ( is_a( $class, \WC_Abstract_Order::class, true ) ) {
				return self::ORDER;
			}
			if ( is_a( $class, \WC_Product::class, true ) ) {
				return self::PRODUCT;
			}
			if ( is_a( $class, Customer::class, true ) ) {
				return self::USER;
			}
			if ( is_a( $class, \WP_User::class, true ) ) {
				return self::USER;
			}
			if ( is_a( $class, NewUserAccount::class, true ) ) {
				return self::USER;
			}
			if ( is_a( $class, \WC_Memberships_User_Membership::class, true ) ) {
				return self::MEMBERSHIP;
			}
			if ( is_a( $class, \WPDesk\ShopMagicGF\Data\FormData::class, true ) ) {
				return self::FORM;
			}
		}

		return self::SHOP;
	}
}
