<?php

namespace WPDesk\ShopMagic\Placeholder;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\WPThemeResolver;
use WPDesk\ShopMagic\DataSharing\DataProvider;
use WPDesk\ShopMagic\DataSharing\DataReceiver;
use WPDesk\ShopMagic\DataSharing\ProviderReceiverMatcher;
use WPDesk\ShopMagic\Frontend\FrontRenderer;
use WPDesk\ShopMagic\Placeholder\Builtin\Order;
use WPDesk\ShopMagic\Placeholder\Builtin\Customer;
use WPDesk\ShopMagic\Placeholder\Builtin\Product;
use WPDesk\ShopMagic\Placeholder\Builtin\Shop;

/**
 * Register placeholders for automation processing.
 */
final class PlaceholderFactoryCore implements PlaceholderFactory2 {

	/** @var Placeholder[] */
	private static $placeholders = [];

	/**
	 * Use when your placeholder need to use templates
	 *
	 * @param string $template_dir
	 *
	 * @return Renderer
	 * @deprecated use TemplateRendererForPlaceholders::get_placeholder_template_renderer()
	 */
	public static function get_placeholder_template_renderer( string $template_dir ): Renderer {
		return new SimplePhpRenderer(
			new ChainResolver(
				new WPThemeResolver( FrontRenderer::THEME_TEMPLATE_SUBDIR . DIRECTORY_SEPARATOR . $template_dir ),
				new DirResolver(
					__DIR__ . DIRECTORY_SEPARATOR . implode(
						DIRECTORY_SEPARATOR,
						[
							'..',
							'..',
							'templates',
							'placeholder',
							$template_dir,
						]
					)
				)
			)
		);
	}

	/**
	 * @param Placeholder[] $hashmap
	 *
	 * @return Placeholder[]
	 */
	private function append_legacy_placeholders( array $hashmap ): array {
		$legacy = [
			'customer_id'                         => new Customer\CustomerId(),
			'customer_name'                       => new Customer\CustomerName(),
			'customer_first_name'                 => new Customer\CustomerFirstName(),
			'customer_last_name'                  => new Customer\CustomerLastName(),
			'customer_email'                      => new Customer\CustomerEmail(),
			'new_password'                        => new Customer\CustomerNewPassword(),

			'user_id'                             => new Customer\CustomerId(),
			'user_name'                           => new Customer\CustomerName(),
			'user_first_name'                     => new Customer\CustomerFirstName(),
			'user_last_name'                      => new Customer\CustomerLastName(),
			'user_email'                          => new Customer\CustomerEmail(),

			'customer_billing_address'            => new Order\OrderBillingAddress(),
			'customer_billing_address_2'          => new Order\OrderBillingAddress2(),
			'customer_billing_city'               => new Order\OrderBillingCity(),
			'customer_billing_country'            => new Order\OrderBillingCountry(),
			'customer_billing_first_name'         => new Order\OrderBillingFirstName(),
			'customer_billing_formatted_address'  => new Order\OrderBillingFormattedAddress(),
			'customer_billing_last_name'          => new Order\OrderBillingLastName(),
			'customer_billing_postcode'           => new Order\OrderBillingPostCode(),
			'customer_billing_state'              => new Order\OrderBillingState(),

			'customer_shipping_address'           => new Order\OrderShippingAddress(),
			'customer_shipping_address_2'         => new Order\OrderShippingAddress2(),
			'customer_shipping_city'              => new Order\OrderShippingCity(),
			'customer_shipping_country'           => new Order\OrderShippingCountry(),
			'customer_shipping_first_name'        => new Order\OrderShippingFirstName(),
			'customer_shipping_formatted_address' => new Order\OrderShippingFormattedAddress(),
			'customer_shipping_last_name'         => new Order\OrderShippingLastName(),
			'customer_shipping_postcode'          => new Order\OrderShippingPostCode(),
			'customer_shipping_state'             => new Order\OrderShippingState(),

			'order_id'                            => new Order\OrderId(),
			'order_total'                         => new Order\OrderTotal(),
			'order_date'                          => new Order\OrderDateCreated(),

			'products_ordered'                    => new Order\OrderProductsOrdered( self::get_placeholder_template_renderer( 'products_ordered' ) ),
		];

		return array_merge( $hashmap, apply_filters( 'shopmagic/core/placeholders/legacy', $legacy ) );
	}

	/**
	 * @return Placeholder[]
	 */
	private function get_build_in_placeholders(): array {
		if ( empty( self::$placeholders ) ) {
			self::$placeholders = [
				new Customer\CustomerName(),
				new Customer\CustomerFirstName(),
				new Customer\CustomerLastName(),
				new Customer\CustomerEmail(),
				new Customer\CustomerNewPassword(),
				new Customer\CustomerPhone(),
				new Customer\CustomerUnsubscribeUrl(),
				new Customer\CustomerUsername(),

				new Order\OrderCustomerId(),
				new Order\OrderBillingEmail(),
				new Order\OrderBillingAddress(),
				new Order\OrderBillingAddress2(),
				new Order\OrderBillingCompany(),
				new Order\OrderBillingCity(),
				new Order\OrderBillingCountry(),
				new Order\OrderBillingFirstName(),
				new Order\OrderBillingFormattedAddress(),
				new Order\OrderBillingLastName(),
				new Order\OrderBillingPostCode(),
				new Order\OrderBillingState(),

				new Order\OrderShippingAddress(),
				new Order\OrderShippingAddress2(),
				new Order\OrderShippingCompany(),
				new Order\OrderShippingCity(),
				new Order\OrderShippingCountry(),
				new Order\OrderShippingFirstName(),
				new Order\OrderShippingFormattedAddress(),
				new Order\OrderShippingLastName(),
				new Order\OrderShippingPostCode(),
				new Order\OrderShippingState(),
				new Order\OrderShippingMethod(),

				new Order\OrderCustomerNote(),

				new Order\OrderId(),
				new Order\OrderNumber(),
				new Order\OrderTotal(),
				new Order\OrderDateCreated(),
				new Order\OrderDatePaid(),
				new Order\OrderDateCompleted(),
				new Order\OrderAdminUrl(),
				new Order\OrderPaymentUrl(),
				new Order\OrderPaymentMethod(),
				new Order\OrderProductsSku(),
				new Order\OrderProductsOrdered( self::get_placeholder_template_renderer( 'products_ordered' ) ),
				new Order\OrderRelatedProducts( TemplateRendererForPlaceholders::get_placeholder_template_renderer( 'products_ordered' ) ),
				new Order\OrderCrossSells( TemplateRendererForPlaceholders::get_placeholder_template_renderer( 'products_ordered' ) ),
				new Order\OrderDetails(),
				new Order\OrderDownloads(),
				new Order\OrderMeta(),

				new Order\OrderNoteContent(),
				new Order\OrderNoteAuthor(),

				new Product\ProductId(),
				new Product\ProductName(),
				new Product\ProductLink(),
				new Product\ProductMeta(),

				new Shop\ShopTitle(),
				new Shop\ShopDescription(),
				new Shop\ShopUrl(),
			];
		}

		return self::$placeholders;
	}

	public function create_placeholder( DataProvider $provider, string $slug ): Placeholder {
		$hashmap     = $this->get_slug_to_placeholder_hashmap( $provider );
		$placeholder = $hashmap[ $slug ];

		$placeholder->set_provided_data( $provider->get_provided_data() );

		return $placeholder;
	}

	public function is_placeholder_available( DataProvider $provider, string $slug ): bool {
		$hashmap = $this->get_slug_to_placeholder_hashmap( $provider );

		return ! empty( $hashmap[ $slug ] );
	}

	/**
	 * @return Placeholder[]
	 */
	private function get_placeholder_list(): array {
		return apply_filters( 'shopmagic/core/placeholders', $this->get_build_in_placeholders() );
	}

	/**
	 * @return string[]
	 */
	public function get_possible_placeholder_slugs(): array {
		return array_keys( $this->convert_list_to_hashmap( $this->get_placeholder_list() ) );
	}

	/**
	 * Returns placeholder by slug value.
	 *
	 * Warning: this placeholder is not ready to be used. You can ask for fields, name, description but NOT FOR VALUE.
	 * For value use create_placeholder.
	 *
	 * @param string $slug
	 *
	 * @return Placeholder
	 */
	public function get_placeholder_by_slug( string $slug ): Placeholder {
		$hashmap = $this->convert_list_to_hashmap( $this->get_placeholder_list() );

		return $hashmap[ $slug ];
	}

	/**
	 * @param DataProvider $provider
	 *
	 * @return DataReceiver[]|Placeholder[]
	 */
	public function get_placeholder_list_to_handle( DataProvider $provider ): array {
		return ProviderReceiverMatcher::matchReceivers( $provider, $this->get_placeholder_list() );
	}

	/**
	 * Get ordered list and returns hashmap that eases searching.
	 *
	 * @param Placeholder[] $list
	 *
	 * @return Placeholder[]
	 */
	private function convert_list_to_hashmap( array $list ): array {
		$hashmap = [];
		foreach ( $list as $item ) {
			$hashmap[ $item->get_slug() ] = $item;
		}

		return $hashmap;
	}

	/**
	 * @param DataProvider $provider
	 *
	 * @return Placeholder[]
	 */
	private function get_slug_to_placeholder_hashmap( DataProvider $provider ): array {
		$list    = $this->get_placeholder_list_to_handle( $provider );
		$hashmap = $this->convert_list_to_hashmap( $list );

		return $this->append_legacy_placeholders( $hashmap );
	}

	/** @return void */
	public function append_placeholder( Placeholder $placeholder ) {
		$this->get_build_in_placeholders();
		self::$placeholders[] = $placeholder;
	}
}
