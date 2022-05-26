<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\CommunicationList;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\ShopMagic\Admin\Automation\Metabox;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPersistence;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPostType;
use WPDesk\ShopMagic\MarketingLists\Shortcode\FrontendForm;

final class FormShortcodeMetabox implements Metabox {
	const PARAMS_META = '_form_shortcode';

	/** @var Renderer */
	private $renderer;

	public function render( \WP_Post $post ) {
		$additional_params = ( new CommunicationListPersistence( $post->ID ) )
			->get( self::PARAMS_META );

		$this->renderer->output_render(
			'lists_form_metabox',
			[
				'id'           => $post->ID,
				'name'         => ! isset( $additional_params['name'] ) || $additional_params['name'] === 'on',
				'labels'       => ! isset( $additional_params['labels'] ) || $additional_params['labels'] === 'on',
				'double_optin' => isset( $additional_params['doubleOptin'] ),
				'agreement'    => $additional_params['agreement'] ?? '',
			]
		);
	}

	public function save( string $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST[ self::PARAMS_META ] ) ) {
			return;
		}

		$parameters = array_merge(
			FrontendForm::DEFAULT_PARAMS,
			(array) wp_unslash( $_POST[ self::PARAMS_META ] )
		);

		( new CommunicationListPersistence( $post_id ) )
			->set( self::PARAMS_META, $parameters );
	}

	public function initialize( Renderer $renderer ) {
		$this->renderer = $renderer;
		add_action( 'save_post_' . CommunicationListPostType::TYPE, [ $this, 'save' ] );
		add_meta_box(
			'shopmagic_form_metabox',
			__( 'FrontendForm shortcode', 'shopmagic-for-woocommerce' ),
			[
				$this,
				'render',
			],
			CommunicationListPostType::TYPE,
			'side'
		);
	}
}
