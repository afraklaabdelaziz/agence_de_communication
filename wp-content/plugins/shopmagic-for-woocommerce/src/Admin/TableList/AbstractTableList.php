<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\TableList;

use ShopMagicVendor\WPDesk\Forms\Form;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use WPDesk\ShopMagic\Admin\AdminTemplatesResolver;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Database\Abstraction\DAO;
use WPDesk\ShopMagic\Helper\TemplateResolver;

/**
 * Wrapper for WP_List_Table for internal use in ShopMagic.
 * Encapsulates common list table tasks.
 *
 * @internal
 */
abstract class AbstractTableList extends \WP_List_Table {

	/** @var string */
	protected $singular_name;

	/** @var string */
	protected $plural_name;

	/** @var bool */
	protected $use_ajax = false;

	/** @var string */
	protected $item_per_page_option;

	/** @var DAO\PersistenceGateway */
	protected $table;

	/** @var \ShopMagicVendor\WPDesk\Forms\Form\FormWithFields */
	protected $form_filter;

	/** @var bool */
	protected $show_search_box = false;

	/** @var DAO\Collection<DAO\Item> */
	public $items;

	/** @var string Default key used for ordering the table. */
	protected $order_key = 'id';

	/** @return void */
	private function prepare_column_headers() {
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
	}

	/** @return \ShopMagicVendor\WPDesk\Forms\Field[] */
	abstract protected function get_fields(): array;

	final public function __construct( DAO\PersistenceGateway $table ) {
		$this->table = $table;
		parent::__construct(
			[
				'singular' => $this->singular_name,
				'plural'   => $this->plural_name,
				'ajax'     => $this->use_ajax,
			]
		);

		$this->form_filter = new Form\FormWithFields( $this->get_fields(), 'form_filter' );
	}

	/** @return string[] */
	final public function get_table_classes(): array {
		return [ 'widefat', 'striped', $this->_args['plural'] ];
	}

	/**
	 * @return array<string, string>
	 */
	private function set_table_order(): array {
		$order_direction = ( isset( $_GET['order'] ) && $_GET['order'] === 'asc' ) ? 'ASC' : 'DESC';

		if ( isset( $_GET['orderby'] ) ) {
			return [
				sanitize_key( wp_unslash( $_GET['orderby'] ) ) => $order_direction,
			];
		}

		return [
			$this->order_key => $order_direction,
		];
	}

	/** @return void */
	private function process_bulk_action() {
		if ( is_string( $this->current_action() ) && array_key_exists( $this->current_action(), $this->get_bulk_actions() ) ) {

			$callback = [ $this, 'process_' . $this->current_action() ];
			if ( is_callable( $callback ) ) {
				$callback();
			}

			wp_safe_redirect(
				remove_query_arg(
					[ 'action', 'action2', $this->current_action() ]
				)
			);
			exit;
		}
	}

	public function has_items(): bool {
		return ! $this->items->is_empty();
	}

	/** @return void */
	final public function prepare_items() {
		$this->process_bulk_action();

		$this->prepare_column_headers();

		$items_per_page = $this->get_items_per_page( $this->item_per_page_option, 20 );
		$current_page   = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $items_per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = $this->table->get_all(
			$this->set_table_filter(),
			$this->set_table_order(),
			$offset,
			$items_per_page
		);
		$total_items = $this->table->get_count( $this->set_table_filter() );

		$this->set_pagination_args(
			[
				'total_items'    => $total_items,
				'items_per_page' => $items_per_page,
				'total_pages'    => ceil( $total_items / $items_per_page ),
			]
		);
	}

	/** @return array<int, string[]> */
	private function set_table_filter(): array {
		$result = [];

		if ( ! empty( $_GET['s'] ) ) {
			$result[] = [
				'field'     => 'email',
				'condition' => 'LIKE',
				'value'     => '%' . sanitize_text_field( wp_unslash( $_GET['s'] ) ) . '%',
			];
		}

		if ( ! isset( $_GET[ $this->form_filter->get_form_id() ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $this->table_filters_hook( [] );
		}

		// @phpstan-ignore-next-line
		$values_to_filter = array_map( 'sanitize_text_field', wp_unslash( $_GET[ $this->form_filter->get_form_id() ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->form_filter->handle_request( $values_to_filter );

		foreach ( $this->form_filter->get_data() as $key => $filter_data ) {
			if ( empty( $filter_data ) && $filter_data !== '0' ) {
				continue;
			}

			if ( ( $key === 'customer_id' || $key === 'user_id' ) && strpos( $filter_data, CustomerFactory::GUEST_ID_PREFIX ) !== false ) {
				$key = 'guest_id';
			}

			$result[] = [
				'field'     => $key,
				'condition' => '=',
				'value'     => $filter_data,
			];
		}

		return $this->table_filters_hook( $result );
	}

	protected function table_filters_hook( array $result ): array {
		return $result;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string $which
	 * @return void
	 */
	final protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$renderer = new SimplePhpRenderer( ( new AdminTemplatesResolver() )->get_resolver( __DIR__ . '/templates' ) );

			echo $this->form_filter->render_fields( $renderer ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/** @return void */
	final public function search_box( $text, $input_id ) {
		if ( $this->show_search_box ) {
			parent::search_box( $text, $input_id );
		}
	}
}
