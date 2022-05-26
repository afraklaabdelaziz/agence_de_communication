<?php

namespace WPDesk\ShopMagic\Admin\RateNotice;

/**
 * Manages greetings&marketing notices.
 *
 * @package WPDesk\ShopMagic\Admin\RateNotice
 */
final class RateNotices {

	/** @var TwoWeeksNotice[] */
	private $notices;

	public function __construct( array $notices ) {
		$this->notices = $notices;
	}

	public function hooks() {
		foreach ( $this->notices as $notice ) {
			$notice->hooks();
			add_action(
				'admin_notices',
				static function () use ( $notice ) {
					if ( $notice->should_show_message() ) {
						$notice->show_message();
					}
				}
			);
		}
	}
}
