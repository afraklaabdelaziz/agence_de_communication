<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\BasicEvent;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Exception\ReferenceNoLongerAvailableException;

final class OrderNoteAdded extends BasicEvent {

	/** @var \WP_Comment */
	protected $order_note;

	/**
	 * @var int
	 */
	private $is_customer_note = 0;

	/**
	 * @inheritDoc
	 */
	public function get_group_slug() {
		return EventFactory2::GROUP_ORDERS;
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Note Added', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when a new order note is added.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data_domains() {
		return [ \WP_Comment::class, \WC_Order::class, \WP_User::class ];
	}

	/**
	 * @inheritDoc
	 */
	public function get_provided_data() {
		return [
			\WP_Comment::class => $this->get_order_note(),
			\WC_Order::class   => $this->get_order(),
			\WP_User::class    => $this->get_user(),
		];
	}

	/**
	 * @param $comment_id
	 * @param \WP_Comment $comment
	 *
	 * @internal
	 */
	public function process_event( $comment_id, \WP_Comment $comment ) {
		$this->order_note = $comment;

		if ( $comment->comment_type !== 'order_note' || get_post_type( $comment->comment_post_ID ) !== 'shop_order' ) {
			return;
		}

		$order = $this->get_order();

		if ( ! $order ) {
			return;
		}

		// Must manually set prop for OrderNoteType filter because meta field is added after the comment is inserted.
		if ( $this->is_customer_note === 1 ) {
			add_comment_meta( $comment_id, 'is_customer_note', 1 );
		}

		$this->run_actions();
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_filter( 'woocommerce_new_order_note_data', [ $this, 'catch_order_note_filter' ], 20, 2 );
		add_action( 'wp_insert_comment', [ $this, 'process_event' ], 20, 2 );
	}

	/**
	 * @param $data
	 * @param array $args
	 *
	 * @return mixed
	 * @internal
	 */
	public function catch_order_note_filter( $data, $args ) {
		$this->is_customer_note = $args['is_customer_note'];
		return $data;
	}

	private function get_order_note(): \WP_Comment {
		return $this->order_note;
	}


	/**
	 * @return bool|\WC_Order|\WC_Order_Refund
	 */
	private function get_order() {
		return wc_get_order( $this->order_note->comment_post_ID );
	}

	/**
	 * @return false|\WP_User
	 */
	private function get_user() {
		return $this->get_order()->get_user();
	}

	/**
	 * @return array Normalized event data required for Queue serialization.
	 */
	public function jsonSerialize() {
		return array_merge(
			parent::jsonSerialize(),
			[
				'order_note_id' => $this->get_order_note()->comment_ID,
			]
		);
	}

	/**
	 * @param array $serializedJson @see jsonSerialize
	 */
	public function set_from_json( array $serializedJson ) {
		parent::set_from_json( $serializedJson );
		$this->order_note = get_comment( $serializedJson['order_note_id'] );
	}
}
