<?php

namespace WPDesk\ShopMagic\Admin\Automation\Metabox;

use ShopMagicVendor\WPDesk\Forms\Form\FormWithFields;
use ShopMagicVendor\WPDesk\Forms\Renderer\JsonNormalizedRenderer;
use ShopMagicVendor\WPDesk\Persistence\Adapter\ArrayContainer;
use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\DataSharing\DataLayer;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Filter\Filter;
use WPDesk\ShopMagic\Filter\FilterFactory2;
use WPDesk\ShopMagic\FormIntegration;
use WPDesk\ShopMagic\Placeholder\Placeholder;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

/**
 * Events metabox handler.
 *
 * @package WPDesk\ShopMagic\Admin\Automation
 */
final class EventMetabox implements AjaxMetabox {
	const META_KEY_EVENT = '_event_data';

	/** @var EventFactory2 */
	private $event_factory;

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var FormIntegration */
	private $form_integration;

	/** @var FilterFactory2 */
	private $filter_factory;

	/** @var Renderer */
	private $renderer;

	public function __construct(
			EventFactory2 $event_factory,
			FilterFactory2 $filter_factory,
			PlaceholderFactory2 $placeholder_factory,
			FormIntegration $form_integration
	) {
		$this->event_factory       = $event_factory;
		$this->filter_factory      = $filter_factory;
		$this->placeholder_factory = $placeholder_factory;
		$this->form_integration    = $form_integration;
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
			'shopmagic_event_metabox',
			__( 'Event', 'shopmagic-for-woocommerce' ),
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
	private function hooks() {
		add_action( 'save_post_' . AutomationPostType::TYPE, [ $this, 'save' ] );
		add_action( 'wp_ajax_shopmagic_load_event_params', [ $this, 'render_from_post' ] );
	}

	/**
	 * @param Filter[] $filters
	 *
	 * @return array<array{'id': int, 'group': string, 'name': string, 'description': string, 'fields': \ShopMagicVendor\WPDesk\Forms\Field[]}>
	 */
	private function get_normalized_filter_list( array $filters ): array {
		$renderer = new JsonNormalizedRenderer();

		$normalized_filters = [];
		foreach ( $filters as $id => $filter ) {
			$normalized_filters[] = [
				'id'          => $id,
				'group'       => $this->event_factory->event_group_name( $filter->get_group_slug() ),
				'name'        => $filter->get_name(),
				'description' => $filter->get_description(),
				'fields'      => $renderer->render_fields( $filter, [] ),
			];
		}

		return $normalized_filters;
	}

	public function render_from_post() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$automation_id          = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0;
		$automation_persistence = new AutomationPersistence( $automation_id );

		$event_slug = isset( $_POST['event_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['event_slug'] ) ) : '';
		$event      = $this->event_factory->get_event( $event_slug );

		$event_has_changed = $event_slug !== $automation_persistence->get_event_slug();

		$event_form = $this->form_integration->load_form(
			new ArrayContainer( $automation_persistence->get_event_data() ),
			new FormWithFields( $event->get_fields() )
		);
		// phpcs:enable

		$data_layer = new DataLayer( $event );

		echo json_encode(
			[
				'event_box'        => $this->form_integration->get_renderer()->render_fields(
					$event,
					$event_form->get_data(),
					'event'
				),
				'filters'          => $this->get_normalized_filter_list( $this->filter_factory->get_filter_list_to_handle( $data_layer ) ),
				'existing_filters' => $event_has_changed ? [] : $automation_persistence->get_filters_data(),
				'description'      => $event->get_description(),
				'placeholders'     => $this->get_normalized_placeholder_list( $this->placeholder_factory->get_placeholder_list_to_handle( $data_layer ) ),
			]
		);

		wp_die();
	}

	public function save( string $post_id ) {
		$event_slug = isset( $_POST['_event'] ) ? sanitize_text_field( wp_unslash( $_POST['_event'] ) ) : '';
		$event      = $this->event_factory->get_event( $event_slug );

		$event_form      = new FormWithFields( $event->get_fields() );
		$event_from_post = isset( $_POST['event'] ) ? wp_unslash( $_POST['event'] ) : [];
		$event_form->handle_request( $event_from_post ); // @phpstan-ignore-line
		if ( $event_form->is_submitted() && $event_form->is_valid() ) {
			$container = new ArrayContainer( [] );
			$this->form_integration->persists_form( $container, $event_form );

			$persistence = new AutomationPersistence( (int) $post_id );
			$persistence->set_event_data( $container->get_array(), $event_slug );
		}
	}


	/**
	 * @param Placeholder[] $list
	 *
	 * @return array<int, array{'placeholder': string, 'title': string|void, 'dialog_slug': string}>
	 */
	private function get_normalized_placeholder_list( array $list ): array {
		return array_values(
			array_map(
				function ( Placeholder $item ) {
					return [
						'placeholder' => "{{ {$item->get_slug()} }}",
						'title'       => $item->get_description(),
						'dialog_slug' => $item->get_slug(),
					];
				},
				$list
			)
		);
	}

	public function render( \WP_Post $post ) {
		$this->renderer->output_render(
			'event_metabox',
			[
				'events'        => $this->event_factory->get_event_list(),
				'event_slug'    => get_post_meta( $post->ID, '_event', true ),
				'event_factory' => $this->event_factory,
			]
		);
	}

}
