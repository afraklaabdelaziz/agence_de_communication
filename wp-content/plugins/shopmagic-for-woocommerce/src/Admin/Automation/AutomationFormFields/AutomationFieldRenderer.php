<?php

namespace WPDesk\ShopMagic\Admin\Automation\AutomationFormFields;

use ShopMagicVendor\WPDesk\Forms\FieldProvider;
use ShopMagicVendor\WPDesk\Forms\FieldRenderer;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\Resolver;

/**
 * Class AutomationFieldRenderer
 *
 * @package WPDesk\ShopMagic\Admin\Automation\AutomationFormFields
 *
 * @deprecated FormWithField should be used instead.
 */
class AutomationFieldRenderer implements FieldRenderer {

	/** @var Resolver  */
	private $resolver;

	public function __construct( Resolver $resolver = null ) {
		if ( $resolver === null ) {
			$resolver = new ShopMagicFieldResolver();
		}
		$this->resolver = $resolver;
	}

	public function render_fields( FieldProvider $provider, array $fields_data, $name_prefix = '' ) {
		$renderer = new SimplePhpRenderer( apply_filters( 'shopmagic/core/admin/automation_field_resolver', $this->resolver ) );

		$content = '';
		foreach ( $provider->get_fields() as $field ) {
			$content .= $renderer->render(
				$field->should_override_form_template() ? $field->get_template_name() : 'form-field',
				[
					'value'         => $fields_data[ $field->get_name() ] ?? $field->get_default_value(),
					'field'         => $field,
					'name_prefix'   => $name_prefix,
					'renderer'      => $renderer,
					'template_name' => $field->get_template_name(),
				]
			);
		}

		return $content;
	}
}
