<?php

namespace WPDesk\ShopMagic\Admin\Automation\Metabox;

use WPDesk\ShopMagic\Admin\Automation\Metabox;
use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Filter\FilterFactory2;
use WPDesk\ShopMagic\FormIntegration;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

/**
 * Filter metabox handler.
 *
 * @package WPDesk\ShopMagic\Admin\Automation
 */
final class FilterMetabox implements Metabox {

	/** @var FilterFactory2 */
	private $filter_factory;

	/** @var FormIntegration */
	private $form_integration;

	/** @var Renderer */
	private $renderer;

	public function __construct( FilterFactory2 $filter_factory, FormIntegration $form_integration ) {
		$this->filter_factory   = $filter_factory;
		$this->form_integration = $form_integration;
	}

	public function initialize( Renderer $renderer ) {
		$this->renderer = $renderer;
		$this->hooks();
		$this->setup();
	}

	/**
	 * @return void
	 */
	private function setup() {
		add_meta_box(
			'shopmagic_filter_metabox',
			__( 'Filter', 'shopmagic-for-woocommerce' ),
			[
				$this,
				'render',
			],
			'shopmagic_automation',
			'normal'
		);
	}

	/**
	 * @return void
	 */
	public function hooks() {
		add_action( 'save_post_' . AutomationPostType::TYPE, [ $this, 'save' ] );
	}

	/**
	 * @return void
	 */
	public function render( \WP_Post $post ) {
		$this->renderer->output_render( 'filter_metabox' );
	}

	public function save( string $post_id ) {
		$filters = isset( $_POST['_filters'] ) ? wp_unslash( $_POST['_filters'] ) : [];

		$automation_persistence = new AutomationPersistence( (int) $post_id );
		$automation_persistence->save_filters_data( $filters, $this->form_integration, $this->filter_factory );
	}
}
