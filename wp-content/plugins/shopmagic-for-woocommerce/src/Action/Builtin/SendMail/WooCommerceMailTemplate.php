<?php

namespace WPDesk\ShopMagic\Action\Builtin\SendMail;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\WPThemeResolver;
use WPDesk\ShopMagic\Frontend\FrontRenderer;
use WPDesk\ShopMagic\LoggerFactory;

/**
 * WooCommerce mail template.
 *
 * TODO: Integration with \WC_Email class
 *
 * @package ShopMagic
 */
class WooCommerceMailTemplate implements MailTemplate {
	const NAME = 'woocommerce';

	/** @var string */
	private $heading_value;

	/** @var string|null */
	private $unsubscribe_url;

	/**
	 * @param string $heading_value
	 * @param string $unsubscribe_url
	 */
	public function __construct( string $heading_value, string $unsubscribe_url = null ) {
		$this->heading_value   = $heading_value;
		$this->unsubscribe_url = $unsubscribe_url;
	}

	/**
	 * Wrap given content in a WooCommerce mail template.
	 *
	 * @param string $html_content
	 * @param array $args
	 *
	 * @return string
	 */
	public function wrap_content( $html_content, array $args = [] ): string {
		$html_content = $this->wrap_html( $html_content );
		$css          = $this->render_css();

		$html_content = $this->encode_inline_css( $html_content, $css );

		return /**
			 * @ignore WooCommerce hook.
			 */
			apply_filters( 'woocommerce_mail_content', $html_content );
	}

	/**
	 * Wrap html into WC template.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	private function wrap_html( string $html ): string {
		ob_start();

		$this->print_template_part(
			'email-header.php',
			[
				'email_heading' => $this->heading_value,
			]
		);
		echo $html;

		if ( null !== $this->unsubscribe_url ) {
			$append_unsubscribe_link = function ( $content ) {
				return $content . " &middot; <a href='{$this->unsubscribe_url}'>" . __( 'Unsubscribe', 'shopmagic-for-woocommerce' ) . '</a>';
			};
			add_filter( 'woocommerce_email_footer_text', $append_unsubscribe_link );
		}

		$this->print_template_part( 'email-footer.php' );

		if ( null !== $this->unsubscribe_url ) {
			remove_filter( 'woocommerce_email_footer_text', $append_unsubscribe_link );
		}

		return ob_get_clean();
	}

	/**
	 * Prints given WooCommerce template.
	 *
	 * @param string $file
	 * @param array $args
	 */
	private function print_template_part( string $file, array $args = [] ) {

		extract( $args, EXTR_SKIP );

		$template_name = 'emails/' . $file;
		$template_path = '';

		$located = wc_locate_template( 'emails/' . $file, $template_path );

		$located =
			/**
			 * @ignore WooCommerce hook.
			 */
			apply_filters( 'wc_get_template', $located, $template_name, $args, $template_path, '' );

		/**
		 * @ignore WooCommerce hook.
		 */
		do_action( 'woocommerce_before_template_part', $template_name, $template_path, $located, $args );

		include $located;
		/**
		 * @ignore WooCommerce hook.
		 */
		do_action( 'woocommerce_after_template_part', $template_name, $template_path, $located, $args );
	}

	/**
	 * Renders WC css.
	 *
	 * @return string
	 */
	private function render_css(): string {
		ob_start();
		$this->print_template_part( 'email-styles.php' );

		$email_template_dir = 'emails';
		echo ( new SimplePhpRenderer(
			new ChainResolver(
				new DirResolver(
					__DIR__ . DIRECTORY_SEPARATOR . implode(
						DIRECTORY_SEPARATOR,
						[
							'..',
							'..',
							'..',
							'..',
							'templates',
							$email_template_dir,
						]
					)
				),
				new WPThemeResolver( FrontRenderer::THEME_TEMPLATE_SUBDIR . DIRECTORY_SEPARATOR . $email_template_dir )
			)
		) )->render( 'email-styles' );

		return /**
			 * @ignore WooCommerce hook.
			 */
			apply_filters( 'woocommerce_email_styles', ob_get_clean(), $this );
	}

	/**
	 * Insert css into html in a best way possible.
	 *
	 * @param string $html
	 * @param string $css
	 *
	 * @return string HTML with encoded inline css.
	 */
	private function encode_inline_css( string $html, string $css ): string {
		try {
			$emogrifier = EmogrifierFactory::create_Emogrifier( $html, $css );

			return $emogrifier->emogrify();
		} catch ( EmogrifierFactoryNotFoundException $e ) {
			LoggerFactory::get_logger()->error( $e->getMessage(), [ 'exception' => $e ] );

			return '<style type="text/css">' . $css . '</style>' . $html;
		}
	}

}
