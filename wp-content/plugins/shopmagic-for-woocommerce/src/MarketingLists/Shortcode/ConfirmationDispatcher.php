<?php
declare( strict_types=1 );

namespace WPDesk\ShopMagic\MarketingLists\Shortcode;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use WPDesk\ShopMagic\CommunicationList\CommunicationList;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Helper\TemplateResolver;

final class ConfirmationDispatcher {

	/** @var Customer */
	private $customer;

	/** @var CommunicationList */
	private $target_list;

	public function __construct( Customer $customer, CommunicationList $target_list ) {
		$this->customer    = $customer;
		$this->target_list = $target_list;
	}

	public function dispatch_confirmation_email(): bool {
		return (bool) wp_mail(
			$this->customer->get_email(),
			esc_html__( 'Confirm your sign up', 'shopmagic-for-woocommerce' ),
			$this->get_message_content()
		);
	}

	/** @return void */
	public function dispatch_ajax_confirmation() {
		if ( ! wp_doing_ajax() ) {
			return;
		}

		$result = $this->dispatch_confirmation_email();

		if ( ! $result ) {
			wp_send_json_error( esc_html__( 'An error occurred, while sending confirmation message. Ensure, you have entered correct email address.', 'shopmagic-for-woocommerce' ) );
		}

		wp_send_json_success( esc_html__( 'Check your messages box to confirm your sign up.', 'shopmagic-for-woocommerce' ) );
	}

	private function get_message_content(): string {
		return ( new SimplePhpRenderer( TemplateResolver::for_public( 'emails' ) ) )->render(
			'sign_up_confirmation',
			[
				'customer'          => $this->customer,
				'list_id'           => $this->target_list->get_id(),
				'list_title'        => $this->target_list->get_name(),
				'confirmation_link' => $this->get_confirmation_link(),
			]
		);
	}

	private function get_confirmation_link(): string {
		return admin_url( 'admin-post.php' ) . '?' . http_build_query(
			[
				'action'  => 'double_opt_in',
				'hash'    => md5( $this->customer->get_email() . SECURE_AUTH_SALT ),
				'id'      => $this->customer->get_id(),
				'list_id' => $this->target_list->get_id(),
			]
		);
	}
}
