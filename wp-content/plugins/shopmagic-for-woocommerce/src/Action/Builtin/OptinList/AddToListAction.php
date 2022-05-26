<?php

namespace WPDesk\ShopMagic\Action\Builtin\OptinList;

use Psr\Container\NotFoundExceptionInterface;
use ShopMagicVendor\WPDesk\Forms\Field\CheckboxField;
use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Exception\CannotModifyList;
use WPDesk\ShopMagic\Exception\CannotProvideItemException;
use WPDesk\ShopMagic\MarketingLists\Controller\DoubleOptIn;
use WPDesk\ShopMagic\MarketingLists\Shortcode\ConfirmationDispatcher;

final class AddToListAction extends AbstractListAction {
	public function get_name(): string {
		return esc_html__( 'Add E-mail to List', 'shopmagic-for-woocommerce' );
	}

	public function get_fields(): array {
		return array_merge(
			parent::get_fields(),
			[
				( new CheckboxField() )
					->set_name( 'doubleoptin' )
					->set_label( esc_html__( 'Double opt-in', 'shopmagic-for-woocommerce' ) ),
			]
		);
	}

	protected function do_list_action( string $email, int $list_id, string $list_name ): bool {
		try {
			/** @var \WPDesk\ShopMagic\MarketingLists\DAO\ListDTO $customer_opt */
			$customer_opt = $this->table->get_subscribed_to_list( $email, $list_id );
		} catch ( CannotProvideItemException $e ) {
			/** @var \WPDesk\ShopMagic\MarketingLists\DAO\ListDTO $customer_opt */
			$customer_opt = $this->factory->create_for_email_and_list( $email, $list_id );
		}

		if ( $customer_opt->get_id() > 0 && $customer_opt->is_active() ) {
			throw new CannotModifyList(
				sprintf(
					// translators: %1$s Customer email, %2$s Marketing List name.
					esc_html__( 'Customer email %1$s is already subscribed to this list: %2$s.', 'shopmagic-for-woocommerce' ),
					$email,
					$list_name
				)
			);
		}

		try {
			if ( $this->fields_data->has( 'doubleoptin' ) && $this->fields_data->get( 'doubleoptin' ) === CheckboxField::VALUE_TRUE && $this->is_customer_provided() ) {
				global $wpdb;
				$target_list = ( new CommunicationListRepository( $wpdb ) )->get_by_id( $list_id );
				return ( new ConfirmationDispatcher( $this->get_customer(), $target_list ) )
					->dispatch_confirmation_email();
			}
		} catch ( NotFoundExceptionInterface $e ) {
			// Backward compatibility. Process form as if the element wasn't there.
		}

		$customer_opt->set_active( true );
		$this->table->save( $customer_opt );

		if ( $this->logger ) {
			$this->logger->info(
				sprintf(
					// translators: %1$s Customer email, %2$s Marketing List name.
					esc_html__( 'Customer email %1$s successfully added to list: %2$s.', 'shopmagic-for-woocommerce' ),
					$email,
					$list_name
				)
			);
		}

		return true;
	}
}
