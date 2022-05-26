<?php


namespace WPDesk\ShopMagic\Guest;

/**
 * @package WPDesk\ShopMagic\Application
 */
class GuestProductIntegration {
	const PRIORITY_BEFORE_DEFAULT = - 100;

	public function hooks() {
		add_action( 'comment_post', [ $this, 'catch_guest' ], self::PRIORITY_BEFORE_DEFAULT, 2 );
	}

	/**
	 * @param int $comment_ID
	 * @param $approved
	 *
	 * @internal
	 */
	public function catch_guest( $comment_ID, $approved ) {
		global $wpdb;
		$repository = new GuestDAO( $wpdb );
		$factory    = new GuestFactory( $repository );
		$comment    = get_comment( $comment_ID );
		if ( empty( $comment->user_id ) && ! empty( $comment->comment_author_email ) ) {
			$guest = $factory->create_from_email_and_db( $comment->comment_author_email );
			$repository->save( $guest );
		}
	}
}
