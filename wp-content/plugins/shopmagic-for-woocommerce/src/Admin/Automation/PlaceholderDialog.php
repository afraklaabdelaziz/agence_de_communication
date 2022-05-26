<?php
namespace WPDesk\ShopMagic\Admin\Automation;

use ShopMagicVendor\WPDesk\Forms\Resolver\DefaultFormFieldResolver;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Placeholder\PlaceholderVariableParameters;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;

/**
 * UX dialog that helps with placeholder string creation
 *
 * @package WPDesk\ShopMagic\Admin\Automation
 */
final class PlaceholderDialog {

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	public function __construct( PlaceholderFactory2 $placeholder_factory ) {
		$this->placeholder_factory = $placeholder_factory;
	}

	public function hooks() {
		add_action( 'wp_ajax_shopmagic_placeholder_dialog', [ $this, 'output_ajax_dialog' ] );
	}

	/**
	 * @return \ShopMagicVendor\WPDesk\View\Renderer\Renderer
	 */
	private function get_renderer() {
		$chain         = new ChainResolver();
		$resolver_list = [
			new DirResolver( __DIR__ . '/placeholder-dialog-templates' ),
			new DefaultFormFieldResolver(),
		];

		foreach ( $resolver_list as $resolver ) {
			$chain->appendResolver( $resolver );
		}

		return new SimplePhpRenderer( $chain );
	}

	/**
	 * Outputs dialog html. For determining the placeholder to render $_GET['slug'] is used.
	 *
	 * @return never-returns
	 */
	public function output_ajax_dialog() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$placeholder_slug = isset( $_GET['slug'] ) ? sanitize_text_field( wp_unslash( $_GET['slug'] ) ) : '';
		$placeholder      = $this->placeholder_factory->get_placeholder_by_slug( $placeholder_slug );

		$renderer = $this->get_renderer();
		parse_str( isset( $_GET['event_data'] ) ? sanitize_text_field( urldecode( wp_unslash( $_GET['event_data'] ) ) ) : '', $event_data );
		// phpcs:enable

		$content = $renderer->render(
			'dialog-start',
			[
				'slug'        => $placeholder_slug,
				'placeholder' => $placeholder,
			]
		);

		if ( $placeholder instanceof PlaceholderVariableParameters ) {
			$placeholder->set_parameters_values( $event_data );
		}

		foreach ( $placeholder->get_supported_parameters() as $field ) {
			$content .= $renderer->render(
				$field->should_override_form_template() ? $field->get_template_name() : 'form-field',
				[
					'field'         => $field,
					'renderer'      => $renderer,
					'name_prefix'   => '',
					'value'         => $field->get_default_value(),
					'template_name' => $field->get_template_name(),
					'slug'          => $placeholder_slug,
					'placeholder'   => $placeholder,
				]
			);
		}

		$content .= $renderer->render(
			'dialog-end',
			[
				'slug'        => $placeholder_slug,
				'placeholder' => $placeholder,
			]
		);

		die( $content ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
