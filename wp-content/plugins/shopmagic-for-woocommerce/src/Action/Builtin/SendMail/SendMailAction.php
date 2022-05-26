<?php

namespace WPDesk\ShopMagic\Action\Builtin\SendMail;

use Psr\Container\NotFoundExceptionInterface;
use WPDesk\ShopMagic\Admin\Automation\AutomationFormFields\ProEventInfoField;
use WPDesk\ShopMagic\FormField\Field\InputTextField;
use WPDesk\ShopMagic\FormField\Field\SelectField;
use WPDesk\ShopMagic\FormField\Field\WyswigField;
use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;

/**
 * Action to send emails.
 */
final class SendMailAction extends AbstractSendMailAction {
	public function get_name(): string {
		return __( 'Send Email', 'shopmagic-for-woocommerce' );
	}

	protected function get_mail_test_hook_name(): string {
		return 'test_send_mail';
	}

	public function get_fields(): array {
		$fields = [
			( new InputTextField() )
				->set_label( __( 'Heading', 'shopmagic-for-woocommerce' ) )
				->set_name( self::PARAM_HEADING ),
			( new SelectField() )
				->set_label( __( 'Template', 'shopmagic-for-woocommerce' ) )
				->set_name( self::PARAM_TEMPLATE_TYPE )
				->set_options(
					[
						WooCommerceMailTemplate::NAME => __( 'WooCommerce Template', 'shopmagic-for-woocommerce' ),
						PlainMailTemplate::NAME       => __( 'None', 'shopmagic-for-woocommerce' ),
					]
				),
		];

		$notice_name    = 'review-request';
		$time_dismissed = get_user_meta( get_current_user_id(), 'shopmagic_ignore_notice_' . $notice_name, true );
		$show_after     = ( $time_dismissed ) ? $time_dismissed + MONTH_IN_SECONDS : ''; // Will show again after 1 month.
		if ( ( ! WordPressPluggableHelper::is_plugin_active( 'shopmagic-reviews/shopmagic-reviews.php' ) ) && ( time() > $show_after ) ) {
			$product_link = ( get_locale() === 'pl_PL' ) ? // phpcs:ignore Squiz.ControlStructures.InlineIfDeclaration.NotSingleLine
				'https://www.wpdesk.pl/sklep/shopmagic/?utm_source=review-requests&utm_medium=notice&utm_campaign=e08' :
				'https://shopmagic.app/products/shopmagic-review-requests/?utm_source=review-requests&utm_medium=notice&utm_campaign=e08';

			$fields[] = ( new ProEventInfoField() )
				->set_description( '<b>Reminder</b>: with ShopMagic Review Request you can automatically ask for reviews after your customers get their order. <a href="' . $product_link . '" target="_blank">Read more</a>' )
				->add_class( 'notice' )
				->add_class( 'notice-info' )
				->add_class( 'is-dismissible' )
				->set_attribute( 'notice-name', $notice_name );
		}

		$fields[] = ( new WyswigField() )->set_label( __( 'Message', 'shopmagic-for-woocommerce' ) )->set_name( self::PARAM_MESSAGE_TEXT );

		$fields[] = $this->get_unsubscribe_field();

		$fields[] = $this->get_attachment_field();

		return $this->postmark_integration->append_fields_if_enabled(
			array_merge(
				parent::get_fields(),
				$fields
			)
		);
	}

	/**
	 * Creates a template class of a given type.
	 *
	 * @param string $template_type Type to create.
	 *
	 * @return MailTemplate
	 */
	private function create_template( string $template_type ): MailTemplate {
		$heading = $this->fields_data->has( self::PARAM_HEADING ) ? $this->placeholder_processor->process( $this->fields_data->get( self::PARAM_HEADING ) ) : '';
		switch ( $template_type ) {
			case WooCommerceMailTemplate::NAME:
				if ( $this->should_append_unsubscribe() ) {
					$unsubscribe_url = $this->get_unsubscribe_url();
				} else {
					$unsubscribe_url = null;
				}

				/** @var string $heading */
				return new WooCommerceMailTemplate( $heading, $unsubscribe_url );
		}

		return new PlainMailTemplate();
	}

	protected function get_message_content(): string {
		// @todo Placeholder processor may return string[] which we don't want in wpautop. Investigate later.
		// @phpstan-ignore-next-line
		$processed_message = wpautop( $this->placeholder_processor->process( $this->fields_data->get( self::PARAM_MESSAGE_TEXT ) ) );
		$message           =
			/**
			 * @ignore
			 * @see SendPlainTextMailAction
			 */
			apply_filters( 'shopmagic/core/action/sendmail/raw_message', $processed_message );

		try {
			if ( $this->fields_data->has( self::PARAM_TEMPLATE_TYPE ) ) {
				$template_type = $this->placeholder_processor->process( $this->fields_data->get( self::PARAM_TEMPLATE_TYPE ) );
			} else {
				$template_type = PlainMailTemplate::NAME;
			}
		} catch ( NotFoundExceptionInterface $e ) {
			$template_type = PlainMailTemplate::NAME;
		}

		if ( ! is_array( $template_type ) ) {
			$message = $this->create_template( (string) $template_type )->wrap_content( $message );
		}

		if ( $template_type === PlainMailTemplate::NAME && $this->should_append_unsubscribe() ) {
			$unsubscribe_url = $this->get_unsubscribe_url();
			$message        .= "<br /><br /><a href='{$unsubscribe_url}'>" . __( 'Click to unsubscribe', 'shopmagic-for-woocommerce' ) . '</a>';
		}

		return $message;
	}
}
