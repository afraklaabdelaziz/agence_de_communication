<?php

namespace WPDesk\ShopMagic\Admin\Settings;

use WPDesk\ShopMagic\FormField\Field\CheckboxField;
use WPDesk\ShopMagic\FormField\Field\InputTextField;
use WPDesk\ShopMagic\FormField\Field\SubmitField;
use WPDesk\ShopMagic\Tracker\TrackerNotices;

/**
 * ShopMagic settings tab - form with fields to be stored in database.
 *
 * @package WPDesk\ShopMagic\Admin\Settings
 */
final class GeneralSettings extends FieldSettingsTab {
	const OUTCOMES_PURGE = 'enable_outcomes_purge';

	/** @return \ShopMagicVendor\WPDesk\Forms\Field[] */
	protected function get_fields(): array {
		return [
			( new CheckboxField() )
				->set_label( __( 'Help Icon', 'shopmagic-for-woocommerce' ) )
				->set_sublabel( __( 'Disable help icon', 'shopmagic-for-woocommerce' ) )
				->set_description(
					__(
						'Help icon shows only on ShopMagic pages with help articles and ability to ask for help. If you do not want the help icon to display, you can entirely disable it here.',
						'shopmagic-for-woocommerce'
					)
				)
				->set_name( 'disable_beacon' ),

			( new CheckboxField() )
				->set_label( __( 'Usage Data', 'shopmagic-for-woocommerce' ) )
				->set_sublabel( __( 'Enable', 'shopmagic-for-woocommerce' ) )
				->set_description(
					__( 'Help us improve ShopMagic and allow us to collect insensitive plugin usage data', 'shopmagic-for-woocommerce' ) . ', ' .
					'<a href="' . TrackerNotices::USAGE_DATA_URL . '" target="_blank">' .
						__( 'read more', 'shopmagic-for-woocommerce' ) .
					'</a>.'
				)
				->set_name( 'wpdesk_tracker_agree' ),

			( new CheckboxField() )
				->set_label( __( 'Enable session tracking ', 'shopmagic-for-woocommerce' ) )
				->set_default_value( 'yes' )
				->set_description_tip( __( 'Session tracking uses cookies to remember users when they are not signed in. This means carts can be tracked when the user is signed out. ', 'shopmagic-for-woocommerce' ) )
				->set_name( 'enable_session_tracking' ),

			( new CheckboxField() )
				->set_label( __( 'Enable pre-submit data capture ', 'shopmagic-for-woocommerce' ) )
				->set_description_tip( __( 'Capture guest customer data before forms are submitted e.g. during checkout. ', 'shopmagic-for-woocommerce' ) )
				->set_name( 'enable_pre_submit' ),

			( new CheckboxField() )
				->set_label( esc_html__( 'Enable Outcomes clear', 'shopmagic-for-woocommerce' ) )
				->set_description_tip( esc_html__( 'Automatically clear Outcomes after 30 days.', 'shopmagic-for-woocommerce' ) )
				->set_name( self::OUTCOMES_PURGE ),

			( new InputTextField() )
				->set_label( __( '"From" name', 'shopmagic-for-woocommerce' ) )
				->set_name( 'shopmagic_email_from_name' ),

			( new InputTextField() )
				->set_label( __( '"From" email', 'shopmagic-for-woocommerce' ) )
				->set_name( 'shopmagic_email_from_address' ),

			( new SubmitField() )
				->set_name( 'save' )
				->set_label( __( 'Save changes', 'shopmagic-for-woocommerce' ) )
				->add_class( 'button-primary' ),
		];
	}

	public static function get_tab_slug(): string {
		return 'general';
	}

	public function get_tab_name(): string {
		return __( 'General', 'shopmagic-for-woocommerce' );
	}
}
