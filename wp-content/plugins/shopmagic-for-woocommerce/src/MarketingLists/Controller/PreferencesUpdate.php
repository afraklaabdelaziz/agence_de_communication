<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\MarketingLists\Controller;

use ShopMagicVendor\WPDesk\Forms\Field\CheckboxField;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\MarketingLists\PreferencesRoute;

/**
 * Saves communication preferences update for Customer.
 */
final class PreferencesUpdate {

	/** @var ListTable */
	private $list_table;

	public function __construct( ListTable $table ) {
		$this->list_table = $table;
	}

	/** @return void */
	public function hooks() {
		add_action( 'wp_ajax_' . PreferencesRoute::get_slug(), [ $this, 'process_account_preferences' ] );
		add_action( 'wp_ajax_nopriv_' . PreferencesRoute::get_slug(), [ $this, 'process_account_preferences' ] );
		add_action( 'admin_post_' . PreferencesRoute::get_slug(), [ $this, 'process_account_preferences' ] );
		add_action( 'admin_post_nopriv_' . PreferencesRoute::get_slug(), [ $this, 'process_account_preferences' ] );
	}

	/** @return void */
	public function process_account_preferences() {
		$sanitized_post = array_map(
			static function ( $field ) {
				if ( is_array( $field ) ) {
					return array_map( 'sanitize_text_field', $field );
				}

				return sanitize_text_field( $field );
			},
			$_POST['shopmagic_optin'] ?? []
		);
		$email          = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$this->save_opt_changes( $email, $sanitized_post );

		$back_url = add_query_arg( [ 'success' => 1 ], wp_get_referer() );
		wp_safe_redirect( $back_url );
		exit;
	}

	/**
	 * @param string $email
	 * @param string[] $request
	 *
	 * @return void
	 */
	private function save_opt_changes( string $email, array $request ) {
		$preferences = $this->list_table->get_all( [ 'email' => $email ] );

		foreach ( $preferences as $preference ) {
			if ( isset( $request[ $preference->get_list_id() ] ) && $request[ $preference->get_list_id() ] === CheckboxField::VALUE_TRUE ) {
				$preference->set_active( true );
			} else {
				$preference->set_active( false );
			}
			$this->list_table->save( $preference );
		}
	}
}
