<?php

namespace WPDesk\ShopMagic\Admin\Automation\Metabox;

use ShopMagicVendor\WPDesk\Forms\Form\FormWithFields;
use ShopMagicVendor\WPDesk\Persistence\Adapter\ReferenceArrayContainer;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
use WPDesk\ShopMagic\Action\ActionFactory2;
use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\FormField\Field\InputTextField;
use WPDesk\ShopMagic\FormIntegration;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

/**
 * Actions metabox handler.
 *
 * @package WPDesk\ShopMagic\Admin\Automation
 */
final class ActionMetabox implements AjaxMetabox {
	const POST_KEY_ACTIONS = 'actions';
	const META_KEY_ACTIONS = '_actions';

	/**
	 * @var ActionFactory2
	 */
	private $action_factory;

	/** @var FormIntegration */
	private $form_integration;

	/** @var Renderer */
	private $renderer;

	public function __construct( ActionFactory2 $action_factory, FormIntegration $form_integration ) {
		$this->action_factory   = $action_factory;
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
			'shopmagic_action_metabox',
			__( 'Actions', 'shopmagic-for-woocommerce' ),
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
		add_action( 'wp_ajax_shopmagic_load_action_params', [ $this, 'render_from_post' ] );
	}

	/**
	 * AJAX callback which shows action edit code
	 *
	 * @since   1.0.0
	 */
	public function render_from_post() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$action_slug = isset( $_POST['action_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['action_slug'] ) ) : '';
		$action      = $this->action_factory->get_action( $action_slug );

		$action_id = isset( $_POST['action_id'] ) ? absint( $_POST['action_id'] ) : 0;
		$post_id   = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0;
		// phpcs:enable
		$actions_data = get_post_meta( $post_id, self::META_KEY_ACTIONS, true );
		if ( empty( $actions_data ) ) {
			$actions_data = [];
		}
		if ( ! isset( $actions_data[ $action_id ] ) || ! is_array( $actions_data[ $action_id ] ) ) {
			$actions_data[ $action_id ] = [];
		}

		$action_form = $this->form_integration->load_form(
			new ReferenceArrayContainer( $actions_data[ $action_id ] ),
			new FormWithFields( $action->get_fields() )
		);

		if ( $action_form->is_valid() ) {
			$name_prefix = self::POST_KEY_ACTIONS . '[' . $action_id . ']';

			echo json_encode(
				[
					'action_box'   => $this->form_integration->get_renderer()->render_fields(
						$action,
						$action_form->get_data(),
						$name_prefix
					),
					'data_domains' => $action->get_required_data_domains(),
				]
			);
		}

		wp_die();
	}

	public function save( string $post_id ) {
		$meta = [];
		foreach ( $_POST[ self::POST_KEY_ACTIONS ] as $key => $action_data ) {
			if ( is_numeric( $key ) ) {
				if ( ! isset( $meta[ $key ] ) || ! is_array( $meta[ $key ] ) ) {
					$meta[ $key ] = [];
				}
				$this->save_action( $action_data, new ReferenceArrayContainer( $meta[ $key ] ) );
				$meta[ $key ] = apply_filters( 'shopmagic_settings_save', $meta[ $key ], $action_data, null );
			}
		}

		$persistence = new AutomationPersistence( (int) $post_id );
		$persistence->set_actions_data( $meta );
	}

	/**
	 * @param string[] $action_post_data
	 *
	 * @return void
	 */
	private function save_action( array $action_post_data, PersistentContainer $container ) {
		$action_slug = sanitize_text_field( $action_post_data['_action'] );
		$action      = $this->action_factory->get_action( $action_slug );

		$action_form = new FormWithFields( $action->get_fields() );
		$action_form->add_field(
			( new InputTextField() )
						->set_name( '_action_title' )
		);
		$action_form->handle_request( $action_post_data );
		if ( $action_form->is_submitted() && $action_form->is_valid() ) {
			$this->form_integration->persists_form( $container, $action_form );
			$container->set( '_action', $action_slug );
		}
	}

	public function render( \WP_Post $post ) {
		$this->renderer->output_render(
			'action_metabox',
			[
				'available_actions' => $this->action_factory->get_action_list(),
				'post'              => $post,
				'action_factory'    => $this->action_factory,
			]
		);
	}
}
