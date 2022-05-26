<?php

namespace WPDesk\ShopMagic\Action\Builtin\SendMail;

use WPDesk\ShopMagic\FormField\Field\TextAreaField;

/**
 * Action to send emails using plain text. No HTML is allowed.
 */
final class SendPlainTextMailAction extends AbstractSendMailAction {
	public function get_name(): string {
		return __( 'Send Email - Plain Text', 'shopmagic-for-woocommerce' );
	}

	protected function get_mail_test_hook_name(): string {
		return 'test_plain_send_mail';
	}

	public function get_fields(): array {
		return $this->postmark_integration->append_fields_if_enabled(
			array_merge(
				parent::get_fields(),
				[
					( new TextAreaField() )
						->set_label( __( 'Message', 'shopmagic-for-woocommerce' ) )
						->set_name( self::PARAM_MESSAGE_TEXT ),
					$this->get_attachment_field(),
				]
			)
		);
	}

	protected function get_message_content(): string {
		$raw_message =
			/**
			 * Raw email content with processed placeholders. Filter is used in all actions that sends mail.
			 *
			 * @param string $content
			 * @retun string
			 */
			apply_filters( 'shopmagic/core/action/sendmail/raw_message', sanitize_textarea_field( $this->placeholder_processor->process( $this->fields_data->get( self::PARAM_MESSAGE_TEXT ) ) ) );

		return $raw_message;
	}

	public function get_mail_content_type(): string {
		return 'text/plain';
	}
}
