<?php

namespace WPDesk\ShopMagic\Automation;

use ShopMagicVendor\WPDesk\Forms\Form\FormWithFields;
use ShopMagicVendor\WPDesk\Forms\Serializer\ProductSelectSerializer;
use ShopMagicVendor\WPDesk\Persistence\Adapter\ReferenceArrayContainer;
use WPDesk\ShopMagic\Admin\Automation\Metabox\ActionMetabox;
use WPDesk\ShopMagic\Admin\Automation\Metabox\EventMetabox;
use WPDesk\ShopMagic\Filter\ComparisionType\ProductSelectType;
use WPDesk\ShopMagic\Filter\FilterFactory2;
use WPDesk\ShopMagic\FormIntegration;
use WPDesk\ShopMagic\Helper\CapabilitiesCheckTrait;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Responsible for save/load all automation data.
 * TODO: move save/load from Metaboxes here.
 *
 * @package WPDesk\ShopMagic\Automation
 */
final class AutomationPersistence implements PersistentAutomation {
	use CapabilitiesCheckTrait;

	const FILTER_SLUG_KEY  = 'filter_slug';
	const FILTER_DATA_KEY  = 'data';
	const FILTERS_META_KEY = 'filters';

	/** @var int */
	private $id;

	public function __construct( int $automation_id ) {
		$this->id = $automation_id;
	}

	/**
	 * Run to save info about starting the manual action.
	 *
	 * @return void
	 */
	public function set_manual_action_started() {
		if ( ! $this->can_save() ) {
			return;
		}
		update_post_meta( $this->id, 'manual_started', time() );
		update_post_meta( $this->id, 'manual_started_by', get_current_user_id() );
	}

	/**
	 * @return \WP_User|false False when user not exists.
	 */
	public function get_manual_action_user() {
		return get_user_by( 'id', get_post_meta( $this->id, 'manual_started_by', true ) );
	}

	/**
	 * @return string
	 */
	public function get_manual_action_wp_datetime(): string {
		return WordPressFormatHelper::format_wp_datetime( get_post_meta( $this->id, 'manual_started', true ) );
	}

	/**
	 * @return bool
	 */
	public function is_manual_action_ever_started(): bool {
		$started_by = $this->get_manual_action_user();

		return ! empty( $started_by );
	}

	public function get_automation_name(): string {
		return get_the_title( $this->id );
	}

	/**
	 * @param array $filters_data
	 * @param FormIntegration $form_integration
	 * @param FilterFactory2 $filter_factory
	 *
	 * @return void
	 */
	public function save_filters_data( array $filters_data, FormIntegration $form_integration, FilterFactory2 $filter_factory ) {
		if ( ! $this->can_save() ) {
			return;
		}
		$filter_to_save = [];
		foreach ( $filters_data as $or_group_index => $and_group ) {
			$filter_to_save[ $or_group_index ] = [];
			foreach ( $and_group as $and_group_index => $filter_data ) {
				$filter_slug = sanitize_text_field( $filter_data[ self::FILTER_SLUG_KEY ] );
				if ( ! empty( $filter_slug ) ) {
					$filter = $filter_factory->get_filter( $filter_slug );

					$filter_form = new FormWithFields( $filter->get_fields() );
					$filter_form->handle_request( $filter_data );

					if ( $filter_form->is_submitted() && $filter_form->is_valid() ) {
						$filter_to_save[ $or_group_index ][ $and_group_index ] = [ self::FILTER_DATA_KEY => [] ];
						$container = new ReferenceArrayContainer( $filter_to_save[ $or_group_index ][ $and_group_index ][ self::FILTER_DATA_KEY ] );
						$form_integration->persists_form( $container, $filter_form );
						$filter_to_save[ $or_group_index ][ $and_group_index ][ self::FILTER_SLUG_KEY ] = $filter_slug;
					}
				}
			}
		}
		update_post_meta( $this->id, self::FILTERS_META_KEY, $filter_to_save );
		delete_post_meta( $this->id, '_event_product_list' );
		delete_post_meta( $this->id, '_filters_data' );
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	public function set_filters_data( array $data ) {
		if ( ! $this->can_save() ) {
			return;
		}
		update_post_meta( $this->id, self::FILTERS_META_KEY, $data );
	}

	public function get_filters_data(): array {
		$filters_data = get_post_meta( $this->id, self::FILTERS_META_KEY, true );
		if ( is_array( $filters_data ) ) {
			return $filters_data;
		}

		// fallback for old filter persistence.
		$fallback_data = get_post_meta( $this->id, '_filters_data', true );
		if ( is_array( $fallback_data ) && ! empty( $fallback_data[0]['products'] ) ) {
			$fallback_data = $fallback_data[0]['products'];
		} else {
			$fallback_data = get_post_meta( $this->id, '_event_product_list', true );
		}

		if ( is_array( $fallback_data ) ) {
			$serializer = new ProductSelectSerializer();

			return [
				[
					[
						self::FILTER_SLUG_KEY => 'shopmagic_product_purchased_filter',
						self::FILTER_DATA_KEY => [
							ProductSelectType::CONDITION_KEY => 'matches_any',
							ProductSelectType::VALUE_KEY => $serializer->serialize( $fallback_data ),
						],
					],
				],
			];
		}

		return [];
	}

	/** @return string */
	public function get_event_slug(): string {
		return get_post_meta( $this->id, '_event', true );
	}

	public function get_event_data(): array {
		return $this->fallback_to_array( get_post_meta( $this->id, EventMetabox::META_KEY_EVENT, true ) );
	}

	/**
	 * @param array  $data
	 * @param string $slug
	 *
	 * @return void
	 */
	public function set_event_data( array $data, string $slug ) {
		if ( ! $this->can_save() ) {
			return;
		}
		update_post_meta( $this->id, EventMetabox::META_KEY_EVENT, $data );
		update_post_meta( $this->id, '_event', $slug );
	}

	/**
	 * @param mixed $mixed
	 *
	 * @return array
	 */
	private function fallback_to_array( $mixed ): array {
		if ( ! is_array( $mixed ) ) {
			return [];
		}

		return $mixed;
	}

	public function get_actions_data(): array {
		return $this->fallback_to_array( get_post_meta( $this->id, ActionMetabox::META_KEY_ACTIONS, true ) );
	}

	/** @return void */
	public function set_actions_data( array $data ) {
		if ( ! $this->can_save() ) {
			return;
		}
		update_post_meta( $this->id, ActionMetabox::META_KEY_ACTIONS, $data );
	}

	private function can_save(): bool {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		return (bool) $this->allowed_capability();
	}

}
