<?php

namespace WPDesk\ShopMagic\Action\Builtin\SendMail;

/**
 * Plain content as is.
 *
 * @package ShopMagic
 */
class PlainMailTemplate implements MailTemplate {
	const NAME = 'plain';

	/**
	 * Can wrap given content in a template
	 *
	 * @param string $html_content
	 * @param array $args
	 *
	 * @return string
	 */
	public function wrap_content( $html_content, array $args = [] ) {
		return $html_content;
	}

}
