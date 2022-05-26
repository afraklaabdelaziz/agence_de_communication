<?php

namespace WPDesk\ShopMagic\Admin\Queue;

use ShopMagicVendor\WPDesk\Forms\Field\SubmitField;
use ShopMagicVendor\WPDesk\Forms\Resolver\DefaultFormFieldResolver;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\ActionExecution\QueueActionRunner;
use WC_Queue_Interface;
use WPDesk\ShopMagic\Admin\SelectAjaxField\AutomationSelectAjax;
use WPDesk\ShopMagic\Admin\SelectAjaxField\CustomerSelectAjax;
use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;
use ShopMagicVendor\WPDesk\Forms\Form;
use WPDesk\ShopMagic\LoggerFactory;

/**
 * WordPress WP_List_Table for optins list.
 *
 * @package WPDesk\ShopMagic\Admin\CommunicationType
 */
final class TableList extends \WP_List_Table {

	/** @var WC_Queue_Interface */
	private $queue;

	/** @var Form\FormWithFields */
	private $form_filter;

	public function __construct( WC_Queue_Interface $queue ) {
		parent::__construct(
			[
				'singular' => 'automation',
				'plural'   => 'automations',
				'ajax'     => false,
			]
		);
		$this->queue = $queue;

		$this->form_filter = new Form\FormWithFields(
			[
				( new AutomationSelectAjax() )
					->set_name( 'automation_id' ),
				( new CustomerSelectAjax() )
					->set_name( 'customer_id' ),
				( new SubmitField() )
					->set_name( 'submit' )
					->add_class( 'button' )
					->set_label( __( 'Filter', 'shopmagic-for-woocommerce' ) ),
			],
			'form_filter'
		);
		$this->form_filter->set_method( 'GET' );
	}

	/**
	 * Process args used for filtering the queue.
	 *
	 * @return string[]
	 */
	private function prepare_search_args() {
		$search_args = [
			'group'  => QueueActionRunner::QUEUE_GROUP,
			'status' => \ActionScheduler_Store::STATUS_PENDING,
		];

		if ( isset( $_GET[ $this->form_filter->get_form_id() ] ) ) {
			$this->form_filter->handle_request( $_GET[ $this->form_filter->get_form_id() ] );
			$filters_raw_data = $this->form_filter->get_data();
			$regex_search     = [];
			if ( isset( $filters_raw_data['automation_id'] ) ) {
				$regex_search[] = '{"id":' . $filters_raw_data['automation_id'] . '}';
			}
			if ( isset( $filters_raw_data['customer_id'] ) ) {
				$regex_search[] = '"customer_id":"' . $filters_raw_data['customer_id'] . '",';
			}
			$search_args['search'] = implode( ',', $regex_search );
		}

		return $search_args;
	}

	/**
	 * Prepare table list items.
	 *
	 * @global \wpdb $wpdb
	 */
	public function prepare_items() {
		$page_size   = $this->get_items_per_page( 'optins_items_per_page', 15 );
		$search_args = $this->prepare_search_args();

		$total_items = count( $this->queue->search( array_merge( $search_args, [ 'per_page' => - 1 ] ), 'ids' ) );

		$this->prepare_column_headers();
		$page   = $this->get_pagenum();
		$offset = ( $page - 1 ) * $page_size;

		$items = $this->queue->search(
			array_merge(
				$search_args,
				[
					'per_page' => $page_size,
					'offset'   => $offset,
					'orderby'  => 'date',
					'order'    => ( isset( $_GET['order'] ) && $_GET['order'] === 'desc' ) ? 'DESC' : 'ASC',
				]
			),
			OBJECT
		);

		array_walk(
			$items,
			function ( &$item, $index ) {
				$item = [
					'item'  => $item,
					'index' => $index,
				];
			}
		);
		$this->items = $items;

		$this->set_pagination_args(
			[
				'total_items'    => $total_items,
				'items_per_page' => $page_size,
				'total_pages'    => ceil( $total_items / $page_size ),
			]
		);
	}

	/**
	 * Set _column_headers property for table list
	 */
	private function prepare_column_headers() {
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
	}

	/**
	 * Get list columns.
	 *
	 * @return string[]
	 */
	public function get_columns(): array {
		return [
			'id'         => __( 'ID', 'shopmagic-for-woocommerce' ),
			'automation' => __( 'Automation', 'shopmagic-for-woocommerce' ),
			'customer'   => __( 'Customer', 'shopmagic-for-woocommerce' ),
			'action'     => __( 'Action', 'shopmagic-for-woocommerce' ),
			'timestamp'  => __( 'Run Date', 'shopmagic-for-woocommerce' ),
			'options'    => __( 'Options', 'shopmagic-for-woocommerce' ),
		];
	}

	/**
	 * Get a list of sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return [
			'timestamp' => [ 'timestamp', true ],
		];
	}

	/**
	 * @param array $as_action [item => \ActionScheduler_Action, index => int]
	 *
	 * @return string
	 * @internal Table list callback
	 */
	protected function column_cb( $as_action ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%1$s" />', esc_attr( $as_action['index'] ) );
	}

	/**
	 * @param array $as_action [item => \ActionScheduler_Action, index => int]
	 *
	 * @return string
	 * @internal Table list callback
	 */
	protected function column_id( $as_action ) {
		$args = $as_action['item']->get_args();
		if ( isset( $args[4] ) ) { // arg4 is an unique execution id.
			$id = $args[4];
		} else {
			$id = "({$as_action['index']})";
		}

		return '#' . esc_html( $id );
	}

	/**
	 * @param array $as_action [item => \ActionScheduler_Action, index => int]
	 *
	 * @return string
	 *
	 * @internal Table list callback
	 */
	protected function column_automation( $as_action ) {
		$args                   = $as_action['item']->get_args();
		$automation_id          = (int) $args[0]['id'];
		$automation_persistence = new AutomationPersistence( $automation_id );
		$automation_name        = $automation_persistence->get_automation_name();
		$url                    = get_edit_post_link( $automation_id );

		return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $automation_name ) );
	}

	/**
	 * @param array $as_action [item => \ActionScheduler_Action, index => int]
	 *
	 * @return string
	 * @internal Table list callback
	 */
	protected function column_customer( $as_action ) {
		try {
			$args = $as_action['item']->get_args();
			if ( ! empty( $args[5]['customer_id'] ) ) {
				$customer_dao = new CustomerFactory();
				$customer     = $customer_dao->create_from_id( $args[5]['customer_id'] );

				if ( $customer instanceof Customer ) {
					return \WPDesk\ShopMagic\Admin\Outcome\TableList::render_customer_column( $customer );
				}
			}
			// Fallback for old queue items or when guest conversion is in progress.
			if ( ! empty( $args[1]['order_id'] ) ) {
				$order_id = $args[1]['order_id'];
				$order    = wc_get_order( $order_id );
				if ( $order instanceof \WC_Order ) {
					$customer = $order->get_user();
					if ( $customer instanceof \WP_User ) {
						return $this->get_customer_column_from_user( $customer );
					}

					return sprintf(
						'<a href="mailto:%s">%s</a>',
						esc_attr( $order->get_billing_email() ),
						esc_html( $order->get_billing_email() )
					);
				}
			}
			if ( ! empty( $args[1]['user_id'] ) ) {
				$customer = get_user_by( 'id', $args[1]['user_id'] );
				if ( $customer instanceof \WP_User ) {
					return $this->get_customer_column_from_user( $customer );
				}
			}

			return '';
		} catch ( \Throwable $e ) {
			LoggerFactory::get_logger()->error( 'Error in ' . __CLASS__ . '::' . __METHOD__, [ 'exception' => $e ] );

			return __( 'Invalid customer', 'shopmagic-for-woocommerce' );
		}
	}

	private function get_customer_column_from_user( \WP_User $customer ) {
		return sprintf(
			'<a href="%s">%s</a> <a href="mailto:%s">%s</a>',
			esc_url( get_edit_user_link( $customer->ID ) ),
			esc_html( $customer->display_name ),
			esc_attr( $customer->user_email ),
			esc_html( $customer->user_email )
		);
	}

	/**
	 * @param array $as_action [item => \ActionScheduler_Action, index => int]
	 *
	 * @return string
	 * @internal Table list callback
	 */
	protected function column_action( $as_action ) {
		$args          = $as_action['item']->get_args();
		$automation_id = (int) $args[0]['id'];
		$action_index  = (int) $args[3];

		$automation_persistence = new AutomationPersistence( $automation_id );
		$actions_data           = $automation_persistence->get_actions_data();
		if ( ! empty( $actions_data[ $action_index ] ) ) {
			$action_title = $actions_data[ $action_index ]['_action_title'];

			return sprintf( '%s', esc_html( empty( $action_title ) ? '#' . ( $action_index + 1 ) : $action_title ) );
		}

		return '';
	}

	/**
	 * @param array $as_action [item => \ActionScheduler_Action, index => int]
	 *
	 * @return string
	 * @internal Table list callback
	 */
	protected function column_timestamp( $as_action ) {
		$schedule_time = $as_action['item']->get_schedule();
		if ( $schedule_time instanceof \ActionScheduler_Abstract_Schedule ) {
			$timestamp = $schedule_time->get_date();
			if ( $timestamp instanceof \DateTime ) {
				return WordPressFormatHelper::format_wp_datetime_with_seconds( $timestamp );
			}
		}

		return '';
	}

	/**
	 * @param array $as_action [item => \ActionScheduler_Action, index => int]
	 *
	 * @return string
	 *
	 * @internal
	 */
	protected function column_options( $as_action ): string {
		return sprintf(
			'<a class="cancel_queue" data-sure="%1$s" href="%2$s">%3$s</a>',
			sprintf(
				__( 'Are you sure you want to cancel queued automation %s?', 'shopmagic-for-woocommerce' ),
				$this->column_id( $as_action )
			),
			CancelQueueAction::get_url( $as_action ),
			__( 'Cancel', 'shopmagic-for-woocommerce' )
		);
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$renderer = new SimplePhpRenderer(
				new ChainResolver(
					new DirResolver( __DIR__ . '/list-templates' ),
					new DefaultFormFieldResolver(),
					new DirResolver( __DIR__ . '/../SelectAjaxField/templates' )
				)
			);

			echo $this->form_filter->render_fields( $renderer );
		}
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return string[] Array of CSS classes for the table tag.
	 */
	public function get_table_classes() {
		return [ 'widefat', 'striped', $this->_args['plural'] ];
	}
}
