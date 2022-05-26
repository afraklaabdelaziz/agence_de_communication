<?php
namespace WPDesk\ShopMagic\Admin\Automation\Metabox;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\ShopMagic\Admin\Automation\Metabox;

/**
 * ShopMagic Placeholders Meta Box class
 *
 * @package ShopMagic
 * @since   1.0.0
 */
final class PlaceholdersMetabox implements Metabox {

	/** @var Renderer */
	private $renderer;

	public function initialize( Renderer $renderer ) {
		$this->renderer = $renderer;
		$this->setup();
	}

	/** @return void */
	private function setup() {
		add_meta_box(
			'shopmagic_placeholders_metabox',
			__( 'Placeholders', 'shopmagic-for-woocommerce' ),
			[
				$this,
				'render',
			],
			'shopmagic_automation',
			'side'
		);
	}

	/** @return void */
	public function save( string $post_id ) {}

	public function render( \WP_Post $post ) {
		echo $this->renderer->render( 'placeholder_metabox' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
