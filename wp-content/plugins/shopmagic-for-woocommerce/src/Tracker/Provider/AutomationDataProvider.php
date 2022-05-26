<?php

namespace WPDesk\ShopMagic\Tracker\Provider;

use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Automation\AutomationPostType;

/**
 * Provides info about automations.
 *
 * @package WPDesk\ShopMagic\Tracker\Provider
 */
class AutomationDataProvider implements \WPDesk_Tracker_Data_Provider {

	/** @var \wpdb */
	private $wpdb;

	/**
	 * @param \wpdb $wpdb WordPress Database.
	 */
	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @inheritDoc
	 */
	public function get_data() {
		$automations_info = $this->automations_info();

		return [
			'shopmagic_automations'        => $automations_info,
			'shopmagic_automations_counts' => $this->counts( $automations_info ),
		];
	}

	/**
	 * @return array
	 */
	private function automations_info() {
		$post_type       = AutomationPostType::TYPE;
		$automations     = [];
		$automations_ids = $this->wpdb->get_col( "SELECT DISTINCT id FROM {$this->wpdb->posts} p WHERE p.post_type = '{$post_type}' AND p.post_status = 'publish'" );
		foreach ( $automations_ids as $id ) {
			$persistence   = new AutomationPersistence( $id );
			$automations[] = [
				'event'      => $persistence->get_event_slug(),
				'event_data' => $persistence->get_event_data(),
				'filters'    => $this->get_filters_slugs( $persistence ),
				'actions'    => $this->actions_info( $persistence->get_actions_data() ),
			];
		}

		return $automations;
	}

	/**
	 * @param AutomationPersistence $persistence
	 *
	 * @return string[]
	 */
	private function get_filters_slugs( AutomationPersistence $persistence ) {
		$filters_data  = $persistence->get_filters_data();
		$filters_slugs = [];
		if ( is_array( $filters_data ) ) {
			foreach ( $filters_data as $orData ) {
				foreach ( $orData as $filter ) {
					$filters_slugs[] = $filter[ AutomationPersistence::FILTER_SLUG_KEY ];
				}
			}
		}

		return array_unique( $filters_slugs );
	}

	/**
	 * @param array $actions_data
	 *
	 * @return array
	 */
	private function actions_info( $actions_data ) {
		$actions = [];
		if ( is_array( $actions_data ) ) {
			foreach ( $actions_data as $data ) {
				$actions[] = [
					'slug'                => isset( $data['_action'] ) ? $data['_action'] : '',
					'template_type'       => isset( $data['template_type'] ) ? $data['template_type'] : '',
					'action_delayed'      => isset( $data['_action_delayed'] ) ? $data['_action_delayed'] : 'no',
					'delay_schedule_type' => isset( $data['_action_schedule_type'] ) ? $data['_action_schedule_type'] : null,
					'delay_offset'        => isset( $data['_action_delayed_offset_time'] ) ? $data['_action_delayed_offset_time'] : null,
					'delay_step'          => isset( $data['_action_delay_step'] ) ? $data['_action_delay_step'] : null,
					'delay_string'        => isset( $data['_action_variable_string'] ) ? $data['_action_variable_string'] : null,
					'placeholders'        => $this->placeholders_info( $data ),
				];
			}
		}

		return $actions;
	}

	/**
	 * @param array $automations_info
	 *
	 * @return array
	 */
	private function counts( $automations_info ) {
		$placeholders_count = [];
		$actions_count      = [];
		foreach ( $automations_info as $automation ) {
			foreach ( $automation['actions'] as $action ) {
				$action_slug                   = $action['slug'];
				$actions_count[ $action_slug ] = isset( $actions_count[ $action_slug ] ) ? $actions_count[ $action_slug ] + 1 : 1;

				foreach ( $action['placeholders'] as $key => $count ) {
					$placeholders_count[ $key ] = isset( $placeholders_count[ $key ] ) ? $placeholders_count[ $key ] + $count : $count;
				}
			}
		}

		return [
			'automations_all'    => count( $automations_info ),
			'actions_all'        => array_reduce(
				$automations_info,
				static function ( $carry, $automation ) {
					return count( $automation['actions'] ) + $carry;
				},
				0
			),
			'actions_count'      => $actions_count,
			'placeholders_count' => $placeholders_count,
		];
	}

	/**
	 * @param array $action_data
	 *
	 * @return array
	 */
	private function placeholders_info( $action_data ) {
		$placeholders = [];
		foreach ( $action_data as $action_value ) {
			if ( preg_match_all( '/{{[ ]*([^}]+)[ ]*}}/', $action_value, $matches ) ) {
				if ( is_array( $matches[1] ) ) {
					foreach ( $matches[1] as $placeholder_string ) {
						if ( preg_match( '/([a-zA-Z0-9._]+)/', $placeholder_string, $match ) ) {
							$name                  = $match[1];
							$placeholders[ $name ] = isset( $placeholders[ $name ] ) ? $placeholders[ $name ] + 1 : 1;
						}
					}
				}
			}
		}

		return $placeholders;
	}
}

