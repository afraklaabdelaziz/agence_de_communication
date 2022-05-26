<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Integration\ContactForms;

/**
 * Stores data about submitted entry from integrated contact form for further processing.
 *
 * @package WPDesk\ShopMagic\Integration\ContactForms
 */
interface FormEntry extends \JsonSerializable {

	/**
	 * Contact Form entry always should have reachable email field.
	 * If multiple email fields exists, return the first one.
	 *
	 * @return string
	 * @throws \WPDesk\ShopMagic\Exception\FieldNotFound
	 */
	public function get_email(): string;

	/**
	 * Contact Form can contain many fields. This method allows to access any of them.
	 *
	 * @param string $field_name
	 *
	 * @return string
	 *
	 * @throws \WPDesk\ShopMagic\Exception\FieldNotFound
	 */
	public function get_field( string $field_name ): string;

	/**
	 * It should be always possible to identify and match current entry with created contact form.
	 *
	 * @return int
	 */
	public function get_id(): int;
}
