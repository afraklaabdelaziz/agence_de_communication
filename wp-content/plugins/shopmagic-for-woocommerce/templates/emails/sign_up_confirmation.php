<?php
/**
 * @var \WPDesk\ShopMagic\Customer\Customer $customer
 * @var int $list_id
 * @var string $list_title
 * @var string $confirmation_link
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $customer->get_first_name() !== '' ) {
	printf(
		// translators: %s Customer first name.
		esc_html__( 'Hello, %s!', 'shopmagic-for-woocommerce' ),
		esc_html( $customer->get_first_name() )
	);
} else {
	esc_html_e( 'Hello!', 'shopmagic-for-woocommerce' );
}

echo "\n\n";

printf(
	// translators: %1$s Subscription list name.
	// translators: %2$s Site URL.
	esc_html__( 'You have subscribed to list %1$s at %2$s.', 'shopmagic-for-woocommerce' ),
	esc_html( $list_title ),
	esc_url( home_url() )
);

echo "\n\n";

esc_html_e( 'Confirm your sign up by clicking the link below.', 'shopmagic-for-woocommerce' );

echo "\n";

echo esc_url_raw( $confirmation_link );

echo "\n\n\n";

esc_html_e( 'If you have not subscribed for this list, please ignore the message.', 'shopmagic-for-woocommerce' );
