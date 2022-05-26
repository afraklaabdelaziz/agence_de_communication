<?php

namespace WPDesk\ShopMagic\Integration\Mailchimp;

use WPDesk\ShopMagic\Action\BasicAction;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Customer\UserAsCustomer;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\FormField\Field\CheckboxField;
use WPDesk\ShopMagic\FormField\Field\SelectField;
use WPDesk\ShopMagic\LoggerFactory;

/**
 * ShopMagic add to MailChimp list action.
 */
final class AddToMailChimpListAction extends BasicAction {

	public function get_required_data_domains(): array {
		return [ \WP_User::class, \WC_Order::class ];
	}

	public function get_name(): string {
		return __( 'Add Customer to Mailchimp List', 'shopmagic-for-woocommerce' );
	}

	/** @return \ShopMagicVendor\WPDesk\Forms\Field[] */
	public function get_fields(): array {
		$fields = [];

		try {
			$mc_api_key_from_settings = get_option( 'wc_settings_tab_mailchimp_api_key', false );
			$mailchimp                = new APITools( $mc_api_key_from_settings );

			$fields[] = ( new SelectField() )
				->set_name( '_mailchimp_list_id' )
				->set_label( __( 'The default list ID is', 'shopmagic-for-woocommerce' ) )
				->set_options( $mailchimp->get_all_lists_options() );

		} catch ( \Throwable $e ) {
			LoggerFactory::get_logger()->error( "Mailchimp while preparing fields: {$e->getMessage()}", [ 'exception' => $e ] );
		}

		return array_merge(
			$fields,
			[
				( new CheckboxField() )
					->set_name( '_mailchimp_doubleoptin' )
					->set_default_value( get_option( 'wc_settings_tab_mailchimp_double_optin', 'yes' ) )
					->set_label( __( 'Double opt-in', 'shopmagic-for-woocommerce' ) )
					->set_description(
						__(
							'Send customers an opt-in confirmation email when they subscribe. (Unchecking may be against Mailchimp policy.)',
							'shopmagic-for-woocommerce'
						)
					),
			]
		);
	}

	public function execute( Automation $automation, Event $event ): bool {
		try {
			return $this->add_member_to_mailchimp();
		} catch ( \Throwable $e ) {
			LoggerFactory::get_logger()->error( "Mailchimp exception: {$e->getMessage()}", [ 'exception' => $e ] );
			return false;
		}
	}

	private function add_member_to_mailchimp(): bool {
		$list_id = Settings::get_option( 'wc_settings_tab_mailchimp_list_id' );
		/** @var string $doubleoptin */
		$doubleoptin = $this->placeholder_processor->process( $this->fields_data->get( '_mailchimp_doubleoptin' ) );
		$api_key     = Settings::get_option( 'wc_settings_tab_mailchimp_api_key', false );

		$mail_chimp = new APITools( $api_key );
		$mail_chimp->setLogger( LoggerFactory::get_logger() );

		if ( $this->is_order_provided() ) {
			return $mail_chimp->add_member_from_order( $this->get_order(), $list_id, $doubleoptin );
		}

		if ( $this->is_customer_provided() ) {
			if ( ! $this->is_user_guest() && $this->get_customer() instanceof UserAsCustomer ) {
				return $mail_chimp->add_member_from_user_customer( $this->get_customer(), $list_id, $doubleoptin );
			}

			return $mail_chimp->add_member_from_email( $this->get_customer()->get_email(), $list_id, $doubleoptin );
		}

		if ( $this->is_form_provided() ) {
			return $mail_chimp->add_member_from_email( $this->get_form()->get_email(), $list_id, $doubleoptin );
		}

		return false;
	}
}
