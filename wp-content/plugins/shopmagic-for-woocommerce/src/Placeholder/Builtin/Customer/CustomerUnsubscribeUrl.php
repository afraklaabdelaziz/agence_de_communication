<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Customer;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\LoggerFactory;
use WPDesk\ShopMagic\MarketingLists\PreferencesRoute;
use WPDesk\ShopMagic\MarketingLists\View\AccountPreferences;
use WPDesk\ShopMagic\Placeholder\Builtin\UserBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder;

final class CustomerUnsubscribeUrl extends UserBasedPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.unsubscribe_url';
	}

	public function get_description(): string {
		return esc_html__( 'Display link for customer communication preferences page.', 'shopmagic-for-woocommerce' );
	}

	public function value( array $parameters ): string {
		if ( ! $this->is_customer_provided() ) {
			LoggerFactory::get_logger()->error( sprintf( 'No Customer provided for `%s`', $this->get_slug() ) );
			return '';
		}

		return ( new PreferencesRoute() )->create_preferences_url( $this->get_customer() );
	}
}
