<?php

namespace WPDesk\ShopMagic\MarketingLists\Shortcode;

use WPDesk\ShopMagic\Frontend\FrontRenderer;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;

/**
 * Lists signup forms
 */
final class FrontendForm {
	const ASSETS_HANDLE  = 'shopmagic-form';
	const ACTION         = 'sm_subscribe_user_to_list';
	const SHORTCODE      = 'shopmagic_form';
	const DEFAULT_PARAMS = [
		'id'           => 0,
		'name'         => false,
		'labels'       => false,
		'double_optin' => false,
		'agreement'    => '',
	];

	/** @var ListTable */
	private $table;

	public function __construct( ListTable $table = null ) {
		$this->table = $table ?? new ListTable( new ListFactory() );
	}

	/** @return void */
	public function hooks() {
		add_shortcode(
			self::SHORTCODE,
			function( $parameters ): string {
				return $this->render_form( $parameters );
			}
		);
		add_action(
			'wp_enqueue_scripts',
			function() {
				$this->register_scripts();
			}
		);
	}

	private function render_form( $params ): string {
		try {
			$shortcode = new Shortcode( $params );
		} catch ( \Throwable $e ) {
			return '';
		}

		if ( $this->table->is_subscribed_to_list( $this->get_user_email(), $shortcode->list_id ) ) {
			return '';
		}

		$this->print_scripts();
		return ( new FrontRenderer() )->render(
			'lists_form',
			[
				'action'       => self::ACTION,
				'list_id'      => $shortcode->list_id,
				'double_optin' => $shortcode->double_opt_in,
				'show_name'    => $shortcode->show_name,
				'show_labels'  => $shortcode->show_labels,
				'agreement'    => $shortcode->agreement,
			]
		);
	}

	/** @return void */
	private function register_scripts() {
		wp_register_style( self::ASSETS_HANDLE, SHOPMAGIC_PLUGIN_URL . 'assets/css/frontend.css', [], SHOPMAGIC_VERSION );
		wp_register_script( self::ASSETS_HANDLE, SHOPMAGIC_PLUGIN_URL . 'assets/js/lists-form.min.js', [ 'wp-i18n' ], SHOPMAGIC_VERSION, true );
	}

	/** @return void */
	private function print_scripts() {
		wp_enqueue_style( self::ASSETS_HANDLE );
		wp_enqueue_script( self::ASSETS_HANDLE );
		wp_localize_script(
			self::ASSETS_HANDLE,
			'shopmagic_form',
			[ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
		);
	}

	private function get_user_email(): string {
		if ( is_user_logged_in() ) {
			return wp_get_current_user()->user_email;
		}
		// TODO: Add support for guest users.

		return '';
	}

}
