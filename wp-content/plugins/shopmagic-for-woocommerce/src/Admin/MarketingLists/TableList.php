<?php
declare(strict_types=1);

// phpcs:disable WordPress.Security.NonceVerification.Recommended
namespace WPDesk\ShopMagic\Admin\MarketingLists;

use DateTimeImmutable;
use ShopMagicVendor\WPDesk\Forms\Field\SelectField;
use ShopMagicVendor\WPDesk\Forms\Field\SubmitField;
use WP_Post;
use WPDesk\ShopMagic\Admin\Form\Fields\PostAjaxSelect;
use WPDesk\ShopMagic\Admin\TableList\AbstractTableList;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;
use WPDesk\ShopMagic\MarketingLists\DAO\ListDTO;

/**
 * WordPress WP_List_Table for outcome list.
 */
final class TableList extends AbstractTableList {

	/** @var bool */
	protected $show_search_box = true;

	/** @var string */
	protected $singular_name = 'marketing_list_status';

	/** @var string */
	protected $plural_name = 'marketing_lists_status';

	/** @var string */
	protected $item_per_page_option = 'optins_items_per_page';

	protected function get_fields(): array {
		return [
			( new PostAjaxSelect() )
				->set_name( 'list_id' )
				->set_placeholder( esc_html__( 'Select marketing list', 'shopmagic-for-woocommerce' ) ),
			( new SelectField() )
				->set_name( 'type' )
				->set_options(
					[
						''  => esc_html__( 'List type', 'shopmagic-for-woocommerce' ),
						'0' => esc_html__( 'Opt out', 'shopmagic-for-woocommerce' ),
						'1' => esc_html__( 'Opt in', 'shopmagic-for-woocommerce' ),
					]
				),
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
			'email'      => esc_html__( 'Email', 'shopmagic-for-woocommerce' ),
			'list'       => esc_html__( 'List', 'shopmagic-for-woocommerce' ),
			'active'     => esc_html__( 'Subscribed', 'shopmagic-for-woocommerce' ),
			'created'    => esc_html__( 'Created', 'shopmagic-for-woocommerce' ),
			'updated'    => esc_html__( 'Updated', 'shopmagic-for-woocommerce' ),
		];
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

	protected function column_email( ListDTO $list ): string {
		return $list->get_email();
	}

	protected function column_active( ListDTO $list ): string {
		if ( $list->is_active() ) {
			return '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
		}
		return '<mark class="no"><span class="dashicons dashicons-no"></span></mark>';
	}

	protected function column_list( ListDTO $list ): string {
		$post = get_post( $list->get_list_id() );
		if ( ! $post instanceof WP_Post ) {
			return esc_html__( 'List no longer exists', 'shopmagic-for-woocommerce' );
		}

		return sprintf(
			'<a href="%s">%s</a>',
			esc_url( (string) get_edit_post_link( $post->ID ) ),
			esc_html( $post->post_title )
		);
	}

	/** @return array<string, array{string, bool|string}|string> */
	protected function get_sortable_columns(): array {
		return [
			'email'     => [ 'email', false ],
			'list'      => [ 'list_id', false ],
			'active'    => [ 'active', false ],
			'created'   => [ 'created', false ],
			'updated'   => [ 'updated', false ],
		];
	}

	/**
	 * @param ListDTO $item
	 */
	protected function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item->get_id()
		);
	}

	protected function column_updated( ListDTO $list ): string {
		return $this->date_column( $list->get_updated() );
	}

	protected function column_created( ListDTO $list ): string {
		return $this->date_column( $list->get_created() );
	}

	private function date_column( DateTimeImmutable $date ): string {
		return WordPressFormatHelper::format_wp_datetime( $date, 'F j, Y' );
	}

}
