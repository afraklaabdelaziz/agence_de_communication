<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\Form\Fields;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WP_Post;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPostType;

final class PostAjaxSelect extends \ShopMagicVendor\WPDesk\Forms\Field\BasicField implements Hookable {

	public function get_template_name(): string {
		return 'ajax-select';
	}

	public function get_label_for_value( int $post_id ): string {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post ) {
			return $post->post_title;
		}
		return '';
	}

	/** @return void */
	public function render() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['term'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}
		$term = sanitize_text_field( wp_unslash( $_GET['term'] ) );
		// phpcs:enable

		if ( empty( $term ) ) {
			wp_die();
		}

		$query = new \WP_Query(
			[
				's'              => mb_strtolower( $term, 'UTF-8' ),
				'posts_per_page' => 10,
				'post_type'      => CommunicationListPostType::TYPE,
				'post_status'    => 'publish',
				'orderby'        => 'title',
			]
		);

		/** @var \WP_Post[] $lists */
		$lists = $query->get_posts();

		$results = [];
		foreach ( $lists as $automation_post ) {
			$results[ $automation_post->ID ] = get_the_title( $automation_post->ID );
		}

		wp_send_json( $results );
	}

	/** @return void */
	public function hooks() {
		add_action( 'wp_ajax_' . $this->get_name(), [ $this, 'render' ] );
	}
}
