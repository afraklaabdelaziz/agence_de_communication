<?php

namespace WPDesk\ShopMagic\Automation;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Validator\FullyConfiguredValidator;
use WPDesk\ShopMagic\Event\Event2;
use WPDesk\ShopMagic\Filter\FilterLogic;

/**
 * @package WPDesk\ShopMagic\Automation
 */
final class Automation implements \JsonSerializable {

	/** @var int */
	private $id;

	/** @var Event2 */
	private $event;

	/** @var Action[] */
	private $actions;

	/** @var FilterLogic */
	private $filters;

	/** @param Action[]    $actions */
	public function __construct(
		int $automation_id,
		Event2 $event,
		FilterLogic $filters,
		array $actions
	) {
		$this->id      = $automation_id;
		$this->event   = $event;
		$this->filters = $filters;
		$this->actions = $actions;
	}

	public function get_name(): string {
		$post = get_post( $this->id );
		if ( ! is_null( $post ) ) {
			return $post->post_title;
		}
		return '';
	}

	/**
	 * Has real event and at least one action.
	 *
	 * @deprecated Use FullyConfiguredValidator class.
	 * @codeCoverageIgnore
	 */
	public function is_fully_configured(): bool {
		return ( new FullyConfiguredValidator( $this ) )->valid();
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_event(): Event2 {
		return $this->event;
	}

	/** @return Action[] */
	public function get_actions(): array {
		return $this->actions;
	}

	public function get_filters(): FilterLogic {
		return $this->filters;
	}

	public function has_action( int $index ): bool {
		return isset( $this->actions[ $index ] );
	}

	public function get_action( int $index ): Action {
		return $this->actions[ $index ];
	}

	/** @return int[] */
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
		];
	}

	/** @return void */
	public function initialize( Runner $runner ) {
		$this->get_event()->set_runner( $runner );
		$this->get_event()->set_automation( $this );
		$this->get_event()->set_filter_logic( $this->get_filters() );
		$this->get_event()->initialize();
	}

}
