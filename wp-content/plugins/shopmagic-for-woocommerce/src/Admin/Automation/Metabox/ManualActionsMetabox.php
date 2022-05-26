<?php

namespace WPDesk\ShopMagic\Admin\Automation\Metabox;

use WPDesk\ShopMagic\Admin\Automation\ManualActionsConfirmPage;
use WPDesk\ShopMagic\Admin\Automation\Metabox;
use WPDesk\ShopMagic\Automation\AutomationPersistence;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

final class ManualActionsMetabox implements Metabox {

	const SUBMIT_NAME   = 'manual_automation';
	const PRIORITY_LAST = 999;

	/** @var Renderer */
	private $renderer;

	public function initialize( Renderer $renderer ) {
		$this->renderer = $renderer;
		$this->setup();
		$this->hooks();
	}

	/** @return void */
	private function hooks() {
		add_action( 'save_post', [ $this, 'redirect_manual_automation_submit' ], self::PRIORITY_LAST );
	}

	/**
	 * @param int $post_id
	 * @return void
	 *
	 * @internal save_post action
	 */
	public function redirect_manual_automation_submit( $post_id ) {
		if ( current_user_can( 'manage_options' ) && isset( $_POST['post_type'], $_POST[ self::SUBMIT_NAME ] ) && $_POST['post_type'] === AutomationPostType::TYPE ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			wp_safe_redirect(
				admin_url(
					'edit.php?post_type=shopmagic_automation&' . http_build_query(
						[
							'page' => ManualActionsConfirmPage::SLUG,
							'post' => $post_id,
						]
					)
				)
			);
			exit;
		}
	}

	public function save( string $post_id ) {}

	/**
	 * @param \WP_Post $post
	 *
	 * @return void
	 *
	 * @internal shopmagic_manual_actions_metabox render callback
	 */
	public function render( \WP_Post $post ) {
		$persistence = new AutomationPersistence( $post->ID );
		$user        = $persistence->get_manual_action_user();
		if ( $user instanceof \WP_User ) {
			$username = "{$user->first_name} {$user->last_name}";
		} else {
			$username = __( 'User no longer exists', 'shopmagic-for-woocommerce' );
		}

		if ( $persistence->is_manual_action_ever_started() ) {
			echo $this->renderer->render(
				'manual_actions_metabox_done',
				[
					'username'      => $username,
					'datetime'      => $persistence->get_manual_action_wp_datetime(),
					'automation_id' => $post->ID,
				]
			);
		} else {
			echo $this->renderer->render(
				'manual_actions_metabox_run',
				[
					'name' => self::SUBMIT_NAME,
				]
			);
		}
	}

	/** @return void */
	private function setup() {
		add_meta_box(
			'shopmagic_manual_actions_metabox',
			__( 'Manual actions', 'shopmagic-for-woocommerce' ),
			[
				$this,
				'render',
			],
			'shopmagic_automation',
			'side',
			'high'
		);
	}
}
