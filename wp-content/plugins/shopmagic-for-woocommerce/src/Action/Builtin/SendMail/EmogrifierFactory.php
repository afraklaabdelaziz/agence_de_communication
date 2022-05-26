<?php

namespace WPDesk\ShopMagic\Action\Builtin\SendMail;

use ShopMagicVendor\Pelago\Emogrifier;

/**
 * Emogrifier factory. Emogrifier is a class for converting CSS styles into inline style attributes in your HTML code.
 *
 * @package ShopMagic
 */
class EmogrifierFactory {

	/**
	 * Factory method.
	 *
	 * @param string $html HTML to mess with.
	 * @param string $css Css to be inlined and injected into HTML.
	 *
	 * @return Emogrifier
	 * @throws EmogrifierFactoryNotFoundException
	 */
	public static function create_Emogrifier( $html, $css ) {
		if ( ! class_exists( 'DOMDocument' ) ) {
			throw new EmogrifierFactoryNotFoundException( 'Emogrifier is not supported as DOMDocument is not defined' );
		}
		try {
			return new Emogrifier( $html, $css );
		} catch ( \Throwable $e ) {
			throw new EmogrifierFactoryNotFoundException( 'Emogrifier cant be created', $e );
		}
	}

}
