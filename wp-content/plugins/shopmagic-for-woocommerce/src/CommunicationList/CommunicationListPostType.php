<?php

namespace WPDesk\ShopMagic\CommunicationList;

use WP_Post;
use WPDesk\ShopMagic\Admin\CommunicationList\FormShortcodeMetabox;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\MarketingLists\Shortcode\FrontendForm;

/**
 * Communication type taxonomy definition. Should be hooked with AutomationPostType
 *
 * @since 2.8
 */
final class CommunicationListPostType {
	const TYPE               = 'shopmagic_list';
	const POST_TYPE_MENU_URL = 'edit.php?post_type=' . self::TYPE;

	/** @internal */
	const DATE_COLUMN_KEY = 'date';
	/** @internal */
	const TYPE_COLUMN_KEY = 'type';
	/** @internal */
	const ACTIVE_COLUMN_KEY    = 'active';
	const SHORTCODE_COLUMN_KEY = 'shortcode';

	/**
	 * Initializes custom post type for List types.
	 *
	 * @internal
	 */
	public function setup_post_type() {
		$labels = [
			'name'               => _x( 'Lists', 'post type general name', 'shopmagic-for-woocommerce' ),
			'singular_name'      => _x( 'List', 'post type singular name', 'shopmagic-for-woocommerce' ),
			'menu_name'          => _x( 'Lists', 'admin menu', 'shopmagic-for-woocommerce' ),
			'name_admin_bar'     => _x( 'Lists', 'add on admin bar', 'shopmagic-for-woocommerce' ),
			'add_new'            => _x( 'Add New', 'list', 'shopmagic-for-woocommerce' ),
			'add_new_item'       => __( 'Add New List', 'shopmagic-for-woocommerce' ),
			'new_item'           => __( 'New List', 'shopmagic-for-woocommerce' ),
			'edit_item'          => __( 'Edit List', 'shopmagic-for-woocommerce' ),
			'view_item'          => __( 'View List', 'shopmagic-for-woocommerce' ),
			'all_items'          => __( 'Lists', 'shopmagic-for-woocommerce' ),
			'search_items'       => __( 'Search Lists', 'shopmagic-for-woocommerce' ),
			'parent_item_colon'  => __( 'Parent Lists:', 'shopmagic-for-woocommerce' ),
			'not_found'          => __( 'No Lists found.', 'shopmagic-for-woocommerce' ),
			'not_found_in_trash' => __( 'No Lists found in Trash.', 'shopmagic-for-woocommerce' ),
		];

		$args = [
			'labels'             => $labels,
			'description'        => __( 'ShopMagic lists.', 'shopmagic-for-woocommerce' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => AutomationPostType::POST_TYPE_MENU_URL,
			'show_in_nav_menus'  => false,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'list' ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 10,
			'supports'           => [ 'title' ],
			'taxonomies'         => [],
		];

		register_post_type( self::TYPE, $args );
	}

	/**
	 * Adds 'Event' column header to 'Automations' page and moves date to the last position
	 *
	 * @param array $columns
	 *
	 * @return array new columns
	 * @since 2.8
	 */
	public function set_column_headers( $columns ) {
		unset( $columns[ self::DATE_COLUMN_KEY ] );

		$columns[ self::SHORTCODE_COLUMN_KEY ] = esc_html__( 'Shortcode', 'shopmagic-for-woocommerce' );
		$columns[ self::TYPE_COLUMN_KEY ]      = esc_html__( 'Type', 'shopmagic-for-woocommerce' );
		$columns[ self::ACTIVE_COLUMN_KEY ]    = esc_html__( 'Subscribers', 'shopmagic-for-woocommerce' );

		return $columns;
	}

	/**
	 * @param array<string, string> $columns
	 *
	 * @return array<string, string>
	 */
	public function set_sortable_columns( array $columns ): array {
		$columns[ self::ACTIVE_COLUMN_KEY ] = self::ACTIVE_COLUMN_KEY;
		return $columns;
	}

	/**
	 * Adds 'Event' column content to 'Automations' page
	 *
	 * @param string $column name of column being displayed
	 * @param int $post_id post ID in the row
	 *
	 * @return void
	 */
	public function display_columns_content( $column, $post_id ) {
		$table       = new ListTable( new ListFactory() );
		$persistence = new CommunicationListPersistence( $post_id );

		switch ( $column ) {
			case self::TYPE_COLUMN_KEY:
				switch ( $persistence->get( CommunicationListPersistence::FIELD_TYPE_KEY ) ) {
					case 'opt_in':
						esc_html_e( 'Opt In', 'shopmagic-for-woocommerce' );
						break;
					case 'opt_out':
						esc_html_e( 'Opt Out', 'shopmagic-for-woocommerce' );
						break;
					default:
						esc_html_e( 'Unknown', 'shopmagic-for-woocommerce' );
						break;
				}
				break;
			case self::ACTIVE_COLUMN_KEY:
				echo esc_html( (string) $table->get_count( [ 'list_id' => $post_id ] ) );
				break;
			case self::SHORTCODE_COLUMN_KEY:
				if ( $persistence->get( CommunicationListPersistence::FIELD_TYPE_KEY ) === 'opt_out' ) {
					esc_html_e( 'Not supported for Opt Out lists.', 'shopmagic-for-woocommerce' );
				} else {
					echo strip_tags( $this->display_shortcode( $post_id, $persistence ), '<input>' );
				}
				break;
		}
	}

	private function display_shortcode( int $post_id, CommunicationListPersistence $persistence ): string {
		$shortcode_params = $persistence->get( FormShortcodeMetabox::PARAMS_META );

		$shortcode_string = '[' . FrontendForm::SHORTCODE . ' id="' . $post_id . '"';
		if ( isset( $shortcode_params['name'] ) ) {
			$shortcode_string .= ' name';
		}
		if ( isset( $shortcode_params['labels'] ) ) {
			$shortcode_string .= ' labels';
		}
		if ( isset( $shortcode_params['doubleOptin'] ) ) {
			$shortcode_string .= ' double_optin';
		}

		$shortcode_string .= ']';

		return "<input type='text' style='width: 100%' readonly onclick='this.focus();this.select()' value='{$shortcode_string}'/>";
	}

	public function add_row_action( array $actions, WP_Post $post ): array {
		if ( $post->post_type === self::TYPE ) {
			$trash = $actions['trash'];
			unset( $actions['trash'] );

			$actions['optins'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url(
					AutomationPostType::get_url() . '&' . http_build_query(
						[
							'page'        => 'optins',
							'form_filter' => [ 'list_id' => $post->ID ],
						]
					)
				),
				esc_html__( 'View subscribers', 'shopmagic-for-woocommerce' )
			);

			$actions['trash'] = $trash;
		}

		return $actions;
	}

	/** @return void */
	public function hooks() {
		add_action( 'init', [ $this, 'setup_post_type' ] );
		add_filter( 'manage_' . self::TYPE . '_posts_columns', [ $this, 'set_column_headers' ] );
		add_filter( 'manage_edit-' . self::TYPE . '_sortable_columns', [ $this, 'set_sortable_columns' ] );
		add_action( 'manage_' . self::TYPE . '_posts_custom_column', [ $this, 'display_columns_content' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'add_row_action' ], 10, 2 );
	}
}
