<?php

namespace WPDesk\ShopMagic\CommunicationList;

use ShopMagicVendor\WPDesk\Persistence\FallbackFromGetTrait;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;

/**
 * Do you need to set/get some additional communication data? It's here.
 *
 * TODO: use WordPressPostMeta but fix it first.
 *
 * @package WPDesk\ShopMagic\CommunicationList
 */
final class CommunicationListPersistence implements PersistentContainer {
	use FallbackFromGetTrait;

	const FIELD_TYPE_KEY                 = 'type';
	const FIELD_CHECKOUT_AVAILABLE_KEY   = 'checkout_available';
	const FIELD_CHECKBOX_LABEL_KEY       = 'checkout_label';
	const FIELD_CHECKBOX_DESCRIPTION_KEY = 'checkout_description';

	/** @var int */
	private $post_id;

	public function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	/**
	 * @inheritDoc
	 */
	public function get( $id ) {
		return get_post_meta( $this->post_id, $id, true );
	}

	/**
	 * @inheritDoc
	 */
	public function has( $id ): bool {
		return $this->get( $id ) !== false;
	}

	/**
	 * @inheritDoc
	 */
	public function set( string $id, $value ) {
		update_post_meta( $this->post_id, $id, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function delete( string $id ) {
		delete_post_meta( $this->post_id, $id );
	}
}
