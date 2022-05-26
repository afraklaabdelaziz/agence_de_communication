<?php

namespace WPDesk\ShopMagic\Automation;

use WPDesk\ShopMagic\Event\EventFactory2;

/**
 * ShopMagic's Automation's post type.
 *
 * Stores user defined automations and has additional information
 * to properly handle user automations
 *
 * @package ShopMagic
 * @since   1.0.0
 */
final class AutomationPostType {
	const TYPE               = 'shopmagic_automation';
	const EVENT_COLUMN       = 'event';
	const POST_TYPE_MENU_URL = 'edit.php?post_type=' . self::TYPE;

	/** @var EventFactory2 */
	private $event_factory;

	public static function get_url(): string {
		return admin_url( self::POST_TYPE_MENU_URL );
	}

	public function __construct( EventFactory2 $event_factory ) {
		$this->event_factory = $event_factory;
	}

	/** @return void */
	public function hooks() {
		add_action( 'init', [ $this, 'setup_post_type' ] );

		// Add sortable 'Event' column header and content to 'Automations' page.
		add_filter( 'manage_' . self::TYPE . '_posts_columns', [ $this, 'set_column_headers' ] );
		add_filter( 'manage_edit-' . self::TYPE . '_sortable_columns', [ $this, 'set_sortable_columns' ] );
		add_action( 'manage_' . self::TYPE . '_posts_custom_column', [ $this, 'display_columns_content' ], 10, 2 );
	}

	/**
	 * Initializes custom post type for Automations.
	 *
	 * @return void
	 * @internal
	 */
	public function setup_post_type() {
		$labels = [
			'name'               => _x( 'ShopMagic Automations', 'post type general name', 'shopmagic-for-woocommerce' ),
			'singular_name'      => _x( 'Automation', 'post type singular name', 'shopmagic-for-woocommerce' ),
			'menu_name'          => _x( 'ShopMagic', 'admin menu', 'shopmagic-for-woocommerce' ),
			'name_admin_bar'     => _x( 'Automation', 'add on admin bar', 'shopmagic-for-woocommerce' ),
			'add_new'            => _x( 'Add New', 'automation', 'shopmagic-for-woocommerce' ),
			'add_new_item'       => __( 'Add New Automation', 'shopmagic-for-woocommerce' ),
			'new_item'           => __( 'New Automation', 'shopmagic-for-woocommerce' ),
			'edit_item'          => __( 'Edit Automation', 'shopmagic-for-woocommerce' ),
			'view_item'          => __( 'View Automation', 'shopmagic-for-woocommerce' ),
			'all_items'          => __( 'Automations', 'shopmagic-for-woocommerce' ),
			'search_items'       => __( 'Search Automations', 'shopmagic-for-woocommerce' ),
			'parent_item_colon'  => __( 'Parent Automations:', 'shopmagic-for-woocommerce' ),
			'not_found'          => __( 'No Automations found.', 'shopmagic-for-woocommerce' ),
			'not_found_in_trash' => __( 'No Automations found in Trash.', 'shopmagic-for-woocommerce' ),
		];

		$args = [
			'labels'             => $labels,
			'description'        => __( 'ShopMagic automation rules.', 'shopmagic-for-woocommerce' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'menu_icon'          => 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="black" d="M224 96l16-32 32-16-32-16-16-32-16 32-32 16 32 16 16 32zM80 160l26.66-53.33L160 80l-53.34-26.67L80 0 53.34 53.33 0 80l53.34 26.67L80 160zm352 128l-26.66 53.33L352 368l53.34 26.67L432 448l26.66-53.33L512 368l-53.34-26.67L432 288zm70.62-193.77L417.77 9.38C411.53 3.12 403.34 0 395.15 0c-8.19 0-16.38 3.12-22.63 9.38L9.38 372.52c-12.5 12.5-12.5 32.76 0 45.25l84.85 84.85c6.25 6.25 14.44 9.37 22.62 9.37 8.19 0 16.38-3.12 22.63-9.37l363.14-363.15c12.5-12.48 12.5-32.75 0-45.24zM359.45 203.46l-50.91-50.91 86.6-86.6 50.91 50.91-86.6 86.6z"/></svg>' ),
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'automation' ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
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
	 * @since 2.0.0
	 */
	public function set_column_headers( $columns ) {
		$date = $columns['date'];
		unset( $columns['date'] );

		$columns[ self::EVENT_COLUMN ] = __( 'Event', 'shopmagic-for-woocommerce' );
		$columns['date']               = $date;

		return $columns;
	}

	/**
	 * Makes 'Event' column sortable
	 *
	 * @param array $columns
	 *
	 * @return array sortable columns
	 * @since 2.0.0
	 */
	public function set_sortable_columns( $columns ) {
		$columns[ self::EVENT_COLUMN ] = self::EVENT_COLUMN;

		return $columns;
	}

	/**
	 * Adds 'Event' column content to 'Automations' page
	 *
	 * @param string $column name of column being displayed
	 * @param int $post_id post ID in the row
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public function display_columns_content( $column, $post_id ) {
		if ( self::EVENT_COLUMN === $column ) {
			// @todo: powiązać za pomocą PersistenceFactory
			$event_slug = get_post_meta( $post_id, '_event', true );
			$event      = $this->event_factory->get_event( $event_slug );
			$event_name = $event->get_name();

			echo esc_html( $event_name );
		}
	}

}
