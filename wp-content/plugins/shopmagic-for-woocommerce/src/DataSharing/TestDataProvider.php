<?php

namespace WPDesk\ShopMagic\DataSharing;

use DateTimeImmutable;
use WC_Order_Item_Product;
use WC_Product_Simple;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Guest\GuestFactory;

/**
 * Provides test data.
 */
class TestDataProvider implements DataProvider {
	public function get_provided_data_domains() {
		if ( $this->can_provide_order() ) {
			$domains[] = \WC_Order::class;
			$domains[] = \WP_User::class;
			$domains[] = \WP_Post::class;
			$domains[] = Customer::class;
			$domains[] = \WC_Subscription::class;
		}

		return apply_filters( 'shopmagic/core/test_data_provider/domains', $domains );
	}

	private function get_test_order(): \WC_Order {
		$orders = wc_get_orders(
			[
				'limit'   => 1,
				'orderby' => 'date_created',
				'order'   => 'DESC',
				'status' => array_filter(
					array_keys( wc_get_order_statuses() ),
					static function (string $status): bool {
						return 'wc-refunded' !== $status;
					}
				),
			]
		);

		if ( ! empty( $orders ) ) {
			return reset( $orders );
		}

		return $this->get_stub_order_data();
	}

	/**
	 * @return \WC_Subscription|null
	 */
	private function get_test_subscription() {
		if ( function_exists( 'wcs_get_subscriptions' ) ) {
			$subscriptions = wcs_get_subscriptions(
				[
					'limit'   => 1,
					'orderby' => 'date_created',
					'order'   => 'DESC',
				]
			);

			return reset( $subscriptions );
		}

		return null;
	}

	/**
	 * If provider can really provide an order.
	 *
	 * @return bool
	 */
	public function can_provide_order() {
		return $this->get_test_order() instanceof \WC_Abstract_Order;
	}

	/**
	 * If provider can really provide an subscription.
	 *
	 * @return bool
	 */
	public function can_provide_subscription() {
		return $this->get_test_subscription() instanceof \WC_Subscription;
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data() {
		$data = [];
		if ( $this->can_provide_subscription() ) {
			$subscription                    = $this->get_test_subscription();
			$data[ \WC_Subscription::class ] = $subscription;
		}
		if ( $this->can_provide_order() ) {
			$customer_factory         = new CustomerFactory();
			$test_order               = $this->get_test_order();
			$test_user                = $test_order->get_user();
			$data[ \WC_Order::class ] = $test_order;
			$data[ \WP_Post::class ]  = $test_order;
			if ( $test_user instanceof \WP_User ) {
				$data[ \WP_User::class ] = $test_user;
				$data[ Customer::class ] = $customer_factory->create_from_user_and_order( $test_user, $test_order );
			} else {
				global $wpdb;
				$guest                   = ( new GuestFactory( new GuestDAO( $wpdb ) ) )->create_from_order_and_db( $test_order );
				$data[ Customer::class ] = $customer_factory->create_from_guest( $guest );
			}
		}

		return apply_filters( 'shopmagic/core/test_data_provider/data', $data );
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		return [];
	}

	private function get_stub_order_data(): \WC_Order {
		return new class() extends \WC_Order {

			protected $data = [
				// Abstract order props.
				'parent_id'            => 0,
				'status'               => 'completed',
				'currency'             => 'USD',
				'version'              => '',
				'prices_include_tax'   => false,
				'date_created'         => null,
				'date_modified'        => null,
				'discount_total'       => '0',
				'discount_tax'         => '0',
				'shipping_total'       => '0',
				'shipping_tax'         => '0',
				'cart_tax'             => '0',
				'total'                => '123.3',
				'total_tax'            => '0',

				// Order props.
				'customer_id'          => 0,
				'order_key'            => '',
				'billing'              => [
					'first_name' => 'Andrew',
					'last_name'  => 'Jonte',
					'company'    => 'Acme Inc.',
					'address_1'  => 'Silicon Valley',
					'address_2'  => '23/54',
					'city'       => 'San Francisco',
					'state'      => '',
					'postcode'   => 'AXZ AYX',
					'country'    => 'US',
					'email'      => 'ajonte@acme.com',
					'phone'      => '123123123',
				],
				'shipping'             => [
					'first_name' => 'Andrew',
					'last_name'  => 'Jonte',
					'company'    => 'Acme Inc.',
					'address_1'  => 'Silicon Valley',
					'address_2'  => '23/54',
					'city'       => 'San Francisco',
					'state'      => '',
					'postcode'   => 'AXZ AYX',
					'country'    => 'US',
					'email'      => 'ajonte@acme.com',
					'phone'      => '123123123',
				],
				'payment_method'       => 'bacs',
				'payment_method_title' => 'Bank transfer',
				'transaction_id'       => '',
				'customer_ip_address'  => '',
				'customer_user_agent'  => '',
				'created_via'          => 'checkout',
				'customer_note'        => '',
				'date_completed'       => null,
				'date_paid'            => null,
				'cart_hash'            => '',
			];

			public function get_date_completed( $context = 'view' ): DateTimeImmutable {
				return new DateTimeImmutable( '+2 days' );
			}

			public function get_date_created( $context = 'view' ): DateTimeImmutable {
				return new DateTimeImmutable();
			}

			public function get_date_paid( $context = 'view' ): DateTimeImmutable {
				return new DateTimeImmutable( '+1 day' );
			}

			public function get_items( $types = 'line_item' ): array {
				return [
					new class() extends WC_Order_Item_Product {
						public function get_product() {
							return new class() extends WC_Product_Simple {

								protected $data = [
									'name'               => 'ShopMagic Test Product',
									'slug'               => 'shopmagic-test-product',
									'date_created'       => null,
									'date_modified'      => null,
									'status'             => false,
									'featured'           => false,
									'catalog_visibility' => 'visible',
									'description'        => '',
									'short_description'  => '',
									'sku'                => '',
									'price'              => '',
									'regular_price'      => '',
									'sale_price'         => '',
									'date_on_sale_from'  => null,
									'date_on_sale_to'    => null,
									'total_sales'        => '0',
									'tax_status'         => 'taxable',
									'tax_class'          => '',
									'manage_stock'       => false,
									'stock_quantity'     => null,
									'stock_status'       => 'instock',
									'backorders'         => 'no',
									'low_stock_amount'   => '',
									'sold_individually'  => false,
									'weight'             => '',
									'length'             => '',
									'width'              => '',
									'height'             => '',
									'upsell_ids'         => [],
									'cross_sell_ids'     => [],
									'parent_id'          => 0,
									'reviews_allowed'    => true,
									'purchase_note'      => '',
									'attributes'         => [],
									'default_attributes' => [],
									'menu_order'         => 0,
									'post_password'      => '',
									'virtual'            => false,
									'downloadable'       => false,
									'category_ids'       => [],
									'tag_ids'            => [],
									'shipping_class_id'  => 0,
									'downloads'          => [],
									'image_id'           => '',
									'gallery_image_ids'  => [],
									'download_limit'     => -1,
									'download_expiry'    => -1,
									'rating_counts'      => [],
									'average_rating'     => 0,
									'review_count'       => 0,
								];

							};
						}
					},
				];
			}
		};
	}
}
