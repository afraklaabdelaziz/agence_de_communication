<?php

namespace WPDesk\ShopMagic\Admin\Automation;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\DataSharing\RenderableItemWithPaginationProvider;
use WPDesk\ShopMagic\Event\ManualEvent2;

/**
 * Confirm page after executing ManualActions.
 */
final class ManualActionsConfirmPage {
	const SLUG              = 'mannual-action-confirm';
	const AJAX_MATCH_ACTION = 'ajax_match_items';
	const QUEUE_SPLICE_SIZE = 100;

	/** @var AutomationFactory */
	protected $automation_factory;

	/**
	 * @param AutomationFactory $automation_factory
	 */
	public function __construct( AutomationFactory $automation_factory ) {
		$this->automation_factory = $automation_factory;
	}

	/**
	 * @return void
	 */
	public function hooks() {
		add_action(
			'admin_menu',
			function () {
				add_submenu_page(
					'',
					__( 'Manual action', 'shopmagic-for-woocommerce' ),
					__( 'Manual action', 'shopmagic-for-woocommerce' ),
					'manage_options',
					self::SLUG,
					[ $this, 'render_manual_actions' ]
				);
			}
		);

		add_action( 'wp_ajax_' . self::AJAX_MATCH_ACTION, [ $this, 'ajax_match_items' ] );

		add_action(
			'admin_head',
			static function () {
				remove_submenu_page( AutomationPostType::POST_TYPE_MENU_URL, self::SLUG );
			}
		);

		/**
		 * Filters out custom fields from being duplicated by Duplicate Post plugin in addition to the defaults.
		 *
		 * @param array $meta_excludelist The default exclusion list, based on the “Do not copy these fields” setting, plus some other field names.
		 *
		 * @return array The custom fields to exclude.
		 */
		add_filter(
			'duplicate_post_excludelist_filter',
			static function ( $meta_excludelist ) {
				return array_merge( $meta_excludelist, [ 'manual_started', 'manual_started_by' ] );
			}
		);
	}

	/**
	 * @return void
	 */
	private function trigger_for_matched_items( ManualEvent2 $event ) {
		foreach ( $event->get_items() as $item ) {
			$event->trigger( $item );
		}
	}

	/**
	 * @param Automation $automation
	 * @param array      $ids
	 * @return void
	 */
	private function queue_for_trigger_matched_items( Automation $automation, array $ids ) {
		$queue = \WC_Queue::instance();
		do {
			$slice = array_splice( $ids, 0, self::QUEUE_SPLICE_SIZE );

			$queue->add(
				ManualActionsTriggerQueue::HOOK_TRIGGER_FOR_SLICE,
				[
					$automation->get_id(),
					$slice,
				]
			);

		} while ( count( $ids ) > 0 ); // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found
	}

	/**
	 * @TODO: move to ManualActionsTriggerQueue. This is not a "confirm page"
	 * @return void
	 */
	private function run_manual_actions( AutomationPersistence $persistence, Automation $automation, ManualEvent2 $event ) {
		$persistence->set_manual_action_started();
		$ids = explode( ',', isset( $_POST['ids'] ) ? sanitize_text_field( wp_unslash( $_POST['ids'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $ids ) && $event instanceof RenderableItemWithPaginationProvider ) {
			$this->queue_for_trigger_matched_items( $automation, $ids );
		} else {
			$this->trigger_for_matched_items( $event );
		}
	}

	/**
	 * @internal action
	 * @return void
	 */
	public function render_manual_actions() {
		if ( ! isset( $_GET['post'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$automation = $this->automation_factory->create_automation( (int) $_GET['post'] );
		$automation->initialize( $this->automation_factory->create_runner( $automation ) );

		$event = $automation->get_event();
		if ( ! $event instanceof ManualEvent2 ) {
			return;
		}

		$persistence      = new AutomationPersistence( (int) $automation->get_id() );
		$never_run_before = ! $persistence->is_manual_action_ever_started();
		if ( $never_run_before && isset( $_POST['run'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->run_manual_actions( $persistence, $automation, $event );
			$never_run_before = false;
		}

		$renderer = new SimplePhpRenderer( new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' ) );
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $never_run_before ) {
			if ( $event instanceof RenderableItemWithPaginationProvider ) {
				echo $renderer->render(
					'manual_actions_confirm_queued',
					[
						'automation'              => $automation,
						'item_renderer'           => $event,
						'matched_items_generator' => $event->get_items(),
						'renderer'                => $renderer,
						'ajax_action'             => self::AJAX_MATCH_ACTION,
						'count'                   => $event->get_maximum_possible_count(),
					]
				);
			} else { // fallback for old type manual action - @TODO: remove in 3.0.
				echo $renderer->render(
					'manual_actions_confirm',
					[
						'automation'              => $automation,
						'item_renderer'           => $event,
						'matched_items_generator' => $event->get_items(),
						'renderer'                => $renderer,
					]
				);
			}
		} else {
			echo $renderer->render(
				'manual_actions_done',
				[
					'automation' => $automation,
				]
			);
		}
		// phpcs:enable
	}

	/**
	 * @internal Ajax for item match queue.
	 * @return void
	 */
	public function ajax_match_items() {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$automation = $this->automation_factory->create_automation( (int) $_GET['automation_id'] );
		$automation->initialize( $this->automation_factory->create_runner( $automation ) );

		$event = $automation->get_event();
		if ( $event instanceof RenderableItemWithPaginationProvider ) {
			$rendered_items = [];
			$ids            = [];
			foreach ( $event->get_items_page( (int) $_GET['page'], (int) $_GET['page_size'] ) as $item ) {
				$rendered_items[] = $event->render_item( $item );
				$ids[]            = $item->get_id();
			}
			wp_send_json_success(
				[
					'page'      => (int) $_GET['page'],
					'page_size' => (int) $_GET['page_size'],
					'items'     => $rendered_items,
					'ids'       => $ids,
				]
			);
		}
		// phpcs:enable
	}
}
