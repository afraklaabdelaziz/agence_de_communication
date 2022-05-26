<?php

namespace WPDesk\ShopMagic\Recipe;

use WPDesk\ShopMagic\Automation\AutomationPersistence;

final class RecipeExporter {
	public function get_as_recipe( int $automation_id ): array {
		$automation_persistence = new AutomationPersistence( $automation_id );

		return [
			'name'        => $automation_persistence->get_automation_name(),
			'description' => '',
			'event'       => [
				'slug' => $automation_persistence->get_event_slug(),
				'data' => $automation_persistence->get_event_data(),
			],
			'filters'     => $automation_persistence->get_filters_data(),
			'actions'     => $automation_persistence->get_actions_data(),
		];
	}

	/**
	 * @param int[] $automation_ids
	 *
	 * @return array
	 */
	public function get_multiple_recipes( array $automation_ids ): array {
		$recipes = [];
		foreach ( $automation_ids as $automation_id ) {
			$recipes[] = $this->get_as_recipe( $automation_id );
		}
		return $recipes;
	}
}
