<?php

namespace WPDesk\ShopMagic\Integration\Mailchimp;

use WPDesk\ShopMagic\Admin\Settings\FieldSettingsTab;
use WPDesk\ShopMagic\FormField\Field\CheckboxField;
use WPDesk\ShopMagic\FormField\Field\InputTextField;
use WPDesk\ShopMagic\FormField\Field\Paragraph;
use WPDesk\ShopMagic\FormField\Field\SelectField;
use WPDesk\ShopMagic\FormField\Field\SubmitField;
use WPDesk\ShopMagic\LoggerFactory;

final class Settings extends FieldSettingsTab {
	protected function get_fields() {
		return [
			( new InputTextField() )
				->set_label( __( 'API Key', 'shopmagic-for-woocommerce' ) )
				->set_description_tip(
					__(
						'Insert your API key here which you can create and get from your Mailchimp settings.',
						'shopmagic-for-woocommerce'
					)
				)
				->set_name( 'wc_settings_tab_mailchimp_api_key' ),

			( new SelectField() )
				->set_label( __( 'List', 'shopmagic-for-woocommerce' ) )
				->set_description_tip(
					__(
						'The DEFAULT MailChimp List names to which you want to add clients.',
						'shopmagic-for-woocommerce'
					)
				)
				->set_options( $this->get_mailchimp_lists() )
				->set_name( 'wc_settings_tab_mailchimp_list_id' ),

			( new CheckboxField() )
				->set_label( __( 'Double opt-in', 'shopmagic-for-woocommerce' ) )
				->set_description(
					__(
						'Send customers an opt-in confirmation email when they subscribe. (Unchecking may be against Mailchimp policy.)',
						'shopmagic-for-woocommerce'
					)
				)
				->set_default_value( true )
				->set_name( 'wc_settings_tab_mailchimp_double_optin' ),

			( new InputTextField() )
				->set_label( __( 'Tags', 'shopmagic-for-woocommerce' ) )
				->set_description_tip(
					__(
						'A single text field for seller to include tags (comma separated) to be added to mailchimp upon checkout.',
						'shopmagic-for-woocommerce'
					)
				)
				->set_name( 'wc_settings_tab_mailchimp_tags' ),

			( new Paragraph() )
				->set_description(
					__(
						'Send additional information to Mailchimp list',
						'shopmagic-for-woocommerce'
					)
				),

			( new CheckboxField() )
				->set_label( __( 'Last name', 'shopmagic-for-woocommerce' ) )
				->set_name( 'wc_settings_tab_mailchimp_info_lname' ),
			( new CheckboxField() )
				->set_label( __( 'Address', 'shopmagic-for-woocommerce' ) )
				->set_name( 'wc_settings_tab_mailchimp_info_address' ),
			( new CheckboxField() )
				->set_label( __( 'City', 'shopmagic-for-woocommerce' ) )
				->set_name( 'wc_settings_tab_mailchimp_info_city' ),
			( new CheckboxField() )
				->set_label( __( 'State', 'shopmagic-for-woocommerce' ) )
				->set_name( 'wc_settings_tab_mailchimp_info_state' ),
			( new CheckboxField() )
				->set_label( __( 'Country', 'shopmagic-for-woocommerce' ) )
				->set_name( 'wc_settings_tab_mailchimp_info_country' ),

			( new SubmitField() )
				->set_name( 'save' )
				->set_label( __( 'Save changes', 'shopmagic-for-woocommerce' ) )
				->add_class( 'button-primary' ),
		];
	}

	/**
	 * @return string[]
	 */
	private function get_mailchimp_lists() {
		$mc_apiKey_from_settings = get_option( 'wc_settings_tab_mailchimp_api_key', false );

		try {
			$MailChimpTools      = new APITools( $mc_apiKey_from_settings );
			$lists_names_options = $MailChimpTools->get_all_lists_options();
		} catch ( \Exception $err ) {
			LoggerFactory::get_logger()->info( $err->getMessage(), [ 'exception' => $err ] );
			$lists_names_options = [
				'0' => __( 'Please make sure about the Mailchimp API key provided !', 'shopmagic-for-woocommerce' ),
			];
		}

		return $lists_names_options;
	}

	public static function get_tab_slug() {
		return 'mailchimp';
	}

	public function get_tab_name() {
		return __( 'Mailchimp', 'shopmagic-for-woocommerce' );
	}
}
