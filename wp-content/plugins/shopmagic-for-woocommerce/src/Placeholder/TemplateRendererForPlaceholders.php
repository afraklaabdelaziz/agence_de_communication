<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Placeholder;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\WPThemeResolver;
use WPDesk\ShopMagic\FormField\Field\SelectField;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\ShopMagic\Frontend\FrontRenderer;

/**
 * Enable placeholders to render template from file.
 */
final class TemplateRendererForPlaceholders {

	/** @var Renderer */
	private $renderer;

	public static function get_placeholder_template_renderer( string $template_dir ): TemplateRendererForPlaceholders {
		$chain         = new ChainResolver();
		$resolver_list =
			/**
			 * Use when you want to to change how templates are rendered in in ShopMagic settings.
			 *
			 * @param \ShopMagicVendor\WPDesk\View\Resolver\Resolver[] List of default resolvers. Order is important as the first found will be used.
			 *
			 * @return \ShopMagicVendor\WPDesk\View\Resolver\Resolver[] List of resolvers.
			 * @internal Every template resolver must implement Resolver interface.
			 */
			apply_filters(
				'shopmagic/core/placeholder/template_resolver',
				[
					new WPThemeResolver( FrontRenderer::THEME_TEMPLATE_SUBDIR . DIRECTORY_SEPARATOR . $template_dir ),
					new DirResolver(
						__DIR__ . DIRECTORY_SEPARATOR . implode(
							DIRECTORY_SEPARATOR,
							[
								'..',
								'..',
								'templates',
								'placeholder',
								$template_dir,
							]
						)
					),
				],
				$template_dir
			);
		foreach ( $resolver_list as $resolver ) {
			$chain->appendResolver( $resolver );
		}
		return new self( new SimplePhpRenderer( $chain ) );
	}

	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/** @return array<string, string> */
	private function get_possible_templates(): array {
		return apply_filters(
			'shopmagic/core/placeholder/products_ordered/templates',
			[
				'comma_separated_list' => __( 'Comma separated list', 'shopmagic-for-woocommerce' ),
				'unordered_list'       => __( 'Bullet list', 'shopmagic-for-woocommerce' ),
				'grid_2_col'           => __( 'Grid - 2 columns', 'shopmagic-for-woocommerce' ),
				'grid_3_col'           => __( 'Grid - 3 columns', 'shopmagic-for-woocommerce' ),
			]
		);
	}

	/** @return \ShopMagicVendor\WPDesk\Forms\Field[] */
	public function get_template_selector_field(): array {
		return [
			( new SelectField() )
				->set_name( 'template' )
				->set_label( __( 'Template', 'shopmagic-for-woocommerce' ) )
				->set_options( $this->get_possible_templates() )
				->set_required(),
		];
	}

	/**
	 * @param string|null $template Array key passed may be empty. If so, take first template.
	 * @param mixed[] $array Data injected into template.
	 */
	public function render( $template, array $array ): string {
		$template = $template ?? array_keys( $this->get_possible_templates() )[0];
		return $this->renderer->render( $template, $array );
	}
}
