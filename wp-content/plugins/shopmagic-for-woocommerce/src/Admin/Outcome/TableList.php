<?php
// phpcs:disable WordPress.Security.NonceVerification.Recommended
namespace WPDesk\ShopMagic\Admin\Outcome;

use ShopMagicVendor\WPDesk\Forms\Field;
use ShopMagicVendor\WPDesk\Forms\Field\SubmitField;
use ShopMagicVendor\WPDesk\Forms\Field\SelectField;
use WPDesk\ShopMagic\Admin\SelectAjaxField\AutomationSelectAjax;
use WPDesk\ShopMagic\Admin\SelectAjaxField\CustomerSelectAjax;
use WPDesk\ShopMagic\Admin\TableList\AbstractTableList;
use WPDesk\ShopMagic\AutomationOutcome\Meta\OutcomeMetaFactory;
use WPDesk\ShopMagic\AutomationOutcome\Meta\OutcomeMetaTable;
use WPDesk\ShopMagic\AutomationOutcome\Outcome;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;
use WPDesk\ShopMagic\LoggerFactory;

/**
 * WordPress WP_List_Table for outcome list.
 */
final class TableList extends AbstractTableList {

	/** @var string */
	protected $singular_name = 'outcome';

	/** @var string */
	protected $plural_name = 'outcomes';

	/** @var bool */
	protected $use_ajax = false;

	/** @var string */
	protected $item_per_page_option = 'optins_items_per_page';

	protected $order_key = 'updated';

	/** @return Field[] */
	protected function get_fields(): array {
		return [
			( new SelectField() )
				->set_options(
					[
						''      => __( 'Automation status', 'shopmagic-for-woocommerce' ),
						'1'     => __( 'Completed', 'shopmagic-for-woocommerce' ),
						'0'     => __( 'Failed', 'shopmagic-for-woocommerce' ),
					]
				)
				->set_name( 'success' ),
			( new AutomationSelectAjax() )
				->set_name( 'automation_id' ),
			( new CustomerSelectAjax() )
				->set_name( 'customer_id' ),
			( new SubmitField() )
				->set_name( 'submit' )
				->add_class( 'button' )
				->set_label( __( 'Filter', 'shopmagic-for-woocommerce' ) ),
		];
	}

	/** @return string[] */
	public function get_columns(): array {
		return [
			'cb'         => '<input type="checkbox" />',
			'id'         => __( 'ID', 'shopmagic-for-woocommerce' ),
			'status'     => __( 'Status', 'shopmagic-for-woocommerce' ),
			'automation' => __( 'Automation', 'shopmagic-for-woocommerce' ),
			'customer'   => __( 'Customer', 'shopmagic-for-woocommerce' ),
			'action'     => __( 'Action', 'shopmagic-for-woocommerce' ),
			'timestamp'  => __( 'Date', 'shopmagic-for-woocommerce' ),
			'options'    => __( 'Options', 'shopmagic-for-woocommerce' ),
		];
	}

	protected function table_filters_hook( array $result ): array {
		return array_merge(
			$result,
			[
				[
					'field'     => 'finished',
					'condition' => '=',
					'value'     => '1',
				]
			]

		);
	}

	/** @return string[] */
	protected function get_bulk_actions(): array {
		return [
			'bulk_delete' => esc_html__( 'Delete', 'shopmagic-for-woocommerce' ),
		];
	}

	/** @return void */
	protected function process_bulk_delete() {
		$post_type_object = get_post_type_object( $this->screen->post_type );
		if ( isset( $_GET['bulk-delete'] ) && $post_type_object instanceof \WP_Post_Type && current_user_can( $post_type_object->cap->delete_posts ) ) {
			$delete_ids = array_map( 'absint', wp_unslash( $_GET['bulk-delete'] ) ); // @phpstan-ignore-line
			foreach ( $delete_ids as $id ) {
				$this->table->delete_by_primary( (string) $id );
			}
		}
	}

	/** @param Outcome $outcome */
	protected function column_options( $outcome ): string {
		$meta = new OutcomeMetaTable( new OutcomeMetaFactory() );

		if ( $meta->get_count( [ 'execution_id' => $outcome->get_id() ] ) > 0 ) {
			return sprintf(
				'<a href="%1s">%2s</a>',
				$outcome->get_url(),
				esc_html__( 'View logs', 'shopmagic-for-woocommerce' )
			);
		}

		return '';
	}

	/** @return array<string, array{string, bool}> */
	protected function get_sortable_columns(): array {
		return [
			'timestamp' => [ 'updated', false ],
		];
	}

	/** @param Outcome $item */
	protected function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item->get_id()
		);
	}

	protected function column_automation( Outcome $outcome ): string {
		$url = (string) get_edit_post_link( $outcome->get_automation_id() );

		return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $outcome->get_automation_name() ) );
	}

	public static function render_customer_column( Customer $customer ): string {
		if ( $customer->get_email() === '' && $customer->is_guest() ) {
			return __( 'No customer has been provided', 'shopmagic-for-woocommerce' );
		}

		try {
			if ( $customer->is_guest() ) {
				return sprintf(
					__( 'Guest:', 'shopmagic-for-woocommerce' ) . ' %s <a href="mailto:%s">%s</a>',
					esc_html( $customer->get_full_name() ),
					esc_attr( $customer->get_email() ),
					esc_html( $customer->get_email() )
				);
			}

			return sprintf(
				'<a href="%s">%s</a> <a href="mailto:%s">%s</a>',
				esc_url( get_edit_user_link( (int) $customer->get_id() ) ),
				esc_html( $customer->get_full_name() ),
				esc_attr( $customer->get_email() ),
				esc_html( $customer->get_email() )
			);
		} catch ( \Throwable $e ) {
			LoggerFactory::get_logger()->error( 'Error in ' . __CLASS__ . '::' . __METHOD__, [ 'exception' => $e ] );

			return __( 'Invalid customer', 'shopmagic-for-woocommerce' );
		}
	}

	protected function column_customer( Outcome $outcome ): string {
		return self::render_customer_column( $outcome->get_customer() );
	}

	protected function column_action( Outcome $outcome ): string {
		return esc_html( $outcome->get_action_name() );
	}

	protected function column_id( Outcome $outcome ): string {
		return '#' . esc_html( (string) $outcome->get_id() );
	}

	protected function column_status( Outcome $outcome ): string {
		if ( $outcome->get_success() ) {
			$status_name        = __( 'Completed', 'shopmagic-for-woocommerce' );
			$status_description = __( 'Successfully finished.', 'shopmagic-for-woocommerce' );
			$status_class       = 'completed';
		} else {
			$status_name        = __( 'Failed', 'shopmagic-for-woocommerce' );
			$status_description = __( 'There was an error with executing this action.', 'shopmagic-for-woocommerce' );
			$status_class       = 'failed';
		}

		return sprintf( '<mark class="outcome-status status-%s tips" data-tip="%s"><span>%s</span></mark>', $status_class, $status_description, $status_name );
	}

	protected function column_timestamp( Outcome $outcome ): string {
		$timestamp_format = 'Y-m-d H:i:s';
		$timestamp_format = apply_filters( 'shopmagic/core/outcomes/timestamp_format', $timestamp_format );

		return WordPressFormatHelper::format_wp_datetime( $outcome->get_updated(), $timestamp_format );
	}

}
