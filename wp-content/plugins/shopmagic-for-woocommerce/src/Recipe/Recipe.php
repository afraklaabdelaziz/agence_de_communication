<?php

namespace WPDesk\ShopMagic\Recipe;

use WPDesk\ShopMagic\Automation\AutomationElementLists;
use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Automation\AutomationPostType;

final class Recipe {
	const RECIPE_META_NAME = 'shopmagic_source_recipe';

	/** @var array{name:string,description:string,actions:array,event:array,filters:array} $recipe_data */
	private $recipe_data;

	/** @var string */
	private $id;

	/** @var bool */
	private $can_use;

	/**
	 * @param array{name:string,description:string,actions:array,event:array,filters:array} $recipe_data
	 * @param string                 $id
	 * @param AutomationElementLists $elements
	 */
	public function __construct( array $recipe_data, string $id, AutomationElementLists $elements ) {
		$this->recipe_data = $recipe_data;
		$this->id          = $id;
		$this->can_use     = $this->can_use_event( $elements->get_installed_events() ) &&
							 $this->can_use_actions( $elements->get_installed_actions() ) &&
							 $this->can_use_filters( $elements->get_installed_filters() );
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_name(): string {
		return $this->recipe_data['name'];
	}

	public function get_description(): string {
		return $this->recipe_data['description'];
	}

	public function can_use(): bool {
		return $this->can_use;
	}

	/** @param string[] $events */
	private function can_use_event( array $events ): bool {
		return in_array( $this->recipe_data['event']['slug'], $events, true );
	}

	/** @param string[] $actions */
	private function can_use_actions( array $actions ): bool {
		foreach ( $this->recipe_data['actions'] as $action ) {
			if ( ! in_array( $action['_action'], $actions, true ) ) {
				return false;
			}
		}

		return true;
	}

	/** @param string[] $filters */
	private function can_use_filters( array $filters ): bool {
		foreach ( $this->recipe_data['filters'] as $or ) {
			foreach ( $or as $filter ) {
				if ( ! in_array( $filter['filter_slug'], $filters, true ) ) {
					return false;
				}
			}
		}

		return true;
	}

	public function import(): int {
		$id = wp_insert_post(
			[
				'post_title' => $this->recipe_data['name'],
				'post_type'  => AutomationPostType::TYPE,
			]
		);

		if ( ! is_wp_error( $id ) ) {
			$persistence = new AutomationPersistence( $id );
			$persistence->set_event_data( $this->recipe_data['event']['data'], $this->recipe_data['event']['slug'] );
			$persistence->set_actions_data( $this->recipe_data['actions'] );
			$persistence->set_filters_data( $this->recipe_data['filters'] );
			update_meta( $id, self::RECIPE_META_NAME, $this->id );

			return (int) $id;
		}

		return 0;
	}
}
