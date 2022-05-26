<?php

namespace WPDesk\ShopMagic\Action;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use WPDesk\ShopMagic\DataSharing\Traits\DataReceiverAsProtectedField;
use WPDesk\ShopMagic\DataSharing\Traits\FieldsDataAsProtectedField;
use WPDesk\ShopMagic\DataSharing\Traits\StandardWooCommerceDataProviderAccessors;
use WPDesk\ShopMagic\Placeholder\PlaceholderProcessor;

/**
 * Recommended foundation for actions in ShopMagic. If you want to add a new action you probably shouldn't go any deeper.
 *
 * @package WPDesk\ShopMagic\Action
 */
abstract class BasicAction implements Action, LoggerAwareInterface {
	use DataReceiverAsProtectedField;
	use FieldsDataAsProtectedField;
	use StandardWooCommerceDataProviderAccessors;
	use LoggerAwareTrait;

	/** @var PlaceholderProcessor */
	protected $placeholder_processor;

	/**
	 * Returns field values was previously provided with ::update_fields_data.
	 *
	 * @return ContainerInterface
	 */
	public function get_fields_data() {
		return $this->fields_data;
	}

	public function __clone() {
		$this->fields_data = null;
	}

	/**
	 * @inheritDoc
	 */
	public function set_placeholder_processor( PlaceholderProcessor $processor ) {
		$this->placeholder_processor = $processor;
	}

	/**
	 * Most simple action does not have fields.
	 *
	 * @return \ShopMagicVendor\WPDesk\Forms\Field[]
	 */
	public function get_fields() {
		return [];
	}

	/**
	 * Most simple action does not have any data worth serializing in queue.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return [];
	}
}
