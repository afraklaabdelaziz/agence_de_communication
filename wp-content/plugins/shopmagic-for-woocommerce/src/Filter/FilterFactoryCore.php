<?php

namespace WPDesk\ShopMagic\Filter;

use WPDesk\ShopMagic\DataSharing\DataProvider;
use WPDesk\ShopMagic\DataSharing\DataReceiver;
use WPDesk\ShopMagic\DataSharing\ProviderReceiverMatcher;
use WPDesk\ShopMagic\Event\Imitation\SubscriptionProEvent;
use WPDesk\ShopMagic\Filter\Builtin\Customer\CustomerIdFilter;
use WPDesk\ShopMagic\Filter\Builtin\Customer\CustomerListFilter;
use WPDesk\ShopMagic\Filter\Builtin\Customer\CustomerNotSubscribedToListFilter;
use WPDesk\ShopMagic\Filter\Builtin\Order\OrderNoteContent;
use WPDesk\ShopMagic\Filter\Builtin\Order\OrderNoteType;
use WPDesk\ShopMagic\Filter\Builtin\Order\OrderItems;
use WPDesk\ShopMagic\Filter\Imitation\OrderItemCategoryProEvent;
use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;

/**
 * Attach filters for automations.
 */
final class FilterFactoryCore implements FilterFactory2 {

	/** @var Filter[] */
	private static $filters_cache;

	/** @var bool */
	private $is_pro_active;

	public function __construct( bool $is_pro_active = false ) {
		$this->is_pro_active = $is_pro_active;
	}

	/** @return Filter[] */
	public function get_filter_list(): array {
		if ( empty( self::$filters_cache ) ) {
			self::$filters_cache = apply_filters( 'shopmagic/core/filters', array_merge( $this->get_build_in_filters(), $this->get_pro_preview_filters() ) );
		}

		return self::$filters_cache;
	}

	public function create_filter( string $slug ): Filter {
		return clone $this->get_filter( $slug );
	}

	/**
	 * @return DataReceiver[]&Filter[]
	 */
	public function get_filter_list_to_handle( DataProvider $provider ): array {
		$list = ProviderReceiverMatcher::matchReceivers( $provider, $this->get_filter_list() );
		uasort(
			$list,
			// @phpstan-ignore-next-line
			function ( Filter $a, Filter $b ) {
				$group_compare = strcmp( $a->get_group_slug(), $b->get_group_slug() );
				if ( $group_compare === 0 ) {
					return strcmp( $a->get_name(), $b->get_name() );
				}

				return $group_compare;
			}
		);

		return $list;
	}

	public function get_filter( string $slug ): Filter {
		$filters = $this->get_filter_list();
		if ( isset( $filters[ $slug ] ) ) {
			return apply_filters( 'shopmagic/core/single_filter', $filters[ $slug ] );
		}

		return new NullFilter();
	}

	/**
	 * @return Filter[]
	 */
	private function get_build_in_filters(): array {
		return [
			'shopmagic_product_purchased_filter' => new OrderItems(), // warning - legacy key.
			'order_note_type'                    => new OrderNoteType(),
			'order_note_content'                 => new OrderNoteContent(),
			'customer_id'                        => new CustomerIdFilter(),
			'customer_communication_type'        => new CustomerListFilter(),
			'customer_not_subscribed'            => new CustomerNotSubscribedToListFilter(),
		];
	}

	/** @return Filter[] */
	private function get_pro_preview_filters(): array {
		$filters = [];

		if ( ! $this->is_pro_active && ! WordPressPluggableHelper::is_plugin_active( 'shopmagic-advanced-filters/shopmagic-advanced-filters.php' ) ) {
			$filters['order_item_category'] = new OrderItemCategoryProEvent();
		}
		return $filters;
	}
}
