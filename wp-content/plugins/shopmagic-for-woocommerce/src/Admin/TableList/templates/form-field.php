<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer
 * @var string $name_prefix
 * @var string $value
 *
 * @var string $template_name Real field template.
 */

$renderer->output_render(
	$template_name,
	[
		'field'       => $field,
		'renderer'    => $renderer,
		'name_prefix' => $name_prefix,
		'value'       => $value,
	]
);
