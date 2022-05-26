<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Automation\AutomationListActions;

use WPDesk\ShopMagic\Admin\Automation\ListAction;
use WPDesk\ShopMagic\Automation\AutomationDuplicator;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Exception\ShopMagicException;
use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;

/**
 * Allows end user to duplicate automation post.
 *
 * @package WPDesk\ShopMagic\Admin\Automation\AutomationListActions
 */
final class ActionDuplicate implements ListAction {

	const ACTION_NAME = 'duplicate_automation';

	const COLLIDING_PLUGINS = [
		'duplicate-page/duplicatepage.php',
		'copy-delete-posts/copy-delete-posts.php',
		'duplicate-wp-page-post/duplicate-wp-page-post.php',
		'duplicate-post/duplicate-post.php',
	];

	/** @var AutomationDuplicator */
	private $duplicator;

	public function __construct( AutomationDuplicator $duplicator ) {
		$this->duplicator = $duplicator;
	}

	/** @return void */
	public function hooks() {
		add_filter( 'post_row_actions', [ $this, 'add_action' ], 10, 2 );
		add_action( 'admin_action_' . self::ACTION_NAME, [ $this, 'handle_action' ] );
	}

	/**
	 * @param string[] $actions
	 * @param \WP_Post $post
	 *
	 * @return string[]
	 * @internal
	 */
	public function add_action( array $actions, \WP_Post $post ): array {
		if ( $this->should_add_action() ) {
			$duplicate_link = self::get_duplication_url( $post->ID );

			$actions['duplicate'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $duplicate_link ),
				esc_html__( 'Duplicate', 'shopmagic-for-woocommerce' )
			);
		}

		return $actions;
	}

	/**
	 * @param int   $id
	 * @param string[] $additional_query Action and id keys cannot be overwritten.
	 *
	 * @return string
	 */
	public static function get_duplication_url( int $id, array $additional_query = [] ): string {
		return wp_nonce_url(
			add_query_arg(
				array_merge(
					$additional_query,
					[
						'action' => self::ACTION_NAME,
						'id'     => $id,
					]
				),
				admin_url( 'admin.php' )
			),
			self::ACTION_NAME . $id
		);
	}

	/**
	 * @return void
	 * @internal
	 */
	public function handle_action() {
		$post_id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
		check_admin_referer( self::ACTION_NAME . $post_id );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You cannot duplicate automation. Check your permissions.', 'shopmagic-for-woocommerce' ) );
		}
		try {
			$new_automation_id = $this->duplicator->duplicate( $post_id );
			if ( isset( $_GET['referer'] ) ) {
				$this->redirect_user_to_new_post( $new_automation_id );
			} else {
				$this->redirect_user_back();
			}
			exit;
		} catch ( ShopMagicException $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Check basic permissions and possible collision with other plugins duplicating posts.
	 */
	private function should_add_action(): bool {
		if ( ! current_user_can( 'edit_posts' ) ||
			( get_current_screen() instanceof \WP_Screen && get_current_screen()->post_type !== AutomationPostType::TYPE ) ) {
			return false;
		}

		foreach ( self::COLLIDING_PLUGINS as $colliding_plugin ) {
			if ( WordPressPluggableHelper::is_plugin_active( $colliding_plugin ) ) {
				return false;
			}
		}

		return true;
	}

	/** @return void */
	private function redirect_user_to_new_post( int $id ) {
		wp_safe_redirect(
			add_query_arg(
				[
					'post'   => $id,
					'action' => 'edit',
				],
				admin_url( 'post.php' )
			)
		);
		exit;
	}

	/** @return void */
	private function redirect_user_back() {
		$sendback = wp_get_referer();
		if ( $sendback ) {
			wp_safe_redirect( $sendback );
		} else {
			wp_safe_redirect( admin_url() );
		}
		exit;
	}
}
