<?php

namespace WPDesk\ShopMagic\Action\Builtin\SendMail;

/**
 * Represents a template that can render a content inside.
 *
 * @package ShopMagic
 */
interface MailTemplate {

	/**
	 * Can wrap given content in a template
	 *
	 * @param string $html_content
	 * @param array $args
	 *
	 * @return string
	 */
	public function wrap_content( $html_content, array $args = [] );
}
