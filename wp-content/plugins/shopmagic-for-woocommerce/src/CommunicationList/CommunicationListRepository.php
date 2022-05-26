<?php

namespace WPDesk\ShopMagic\CommunicationList;

use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\Optin\EmailOptModel;
use WPDesk\ShopMagic\Optin\EmailOptRepository;

/**
 * Simple repository pattern for Communication types.
 *
 * @package WPDesk\ShopMagic\CommunicationList
 */
final class CommunicationListRepository {

	/**
	 * @param \wpdb|null $wpdb
	 */
	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @return CommunicationList[]
	 */
	public function get_checkout_communication_types() {
		return array_filter(
			$this->get_all(),
			static function ( $type ) {
				return $type->is_checkout_available() && ! $type->is_opt_out();
			}
		);
	}

	/**
	 * @return CommunicationList[]
	 */
	public function get_account_communication_types() {
		return $this->get_all();
	}

	/**
	 * @return CommunicationList[]
	 */
	public function get_soft_optin_communication_types() {
		return array_filter(
			$this->get_all(),
			static function ( $type ) {
				return $type->is_opt_out();
			}
		);
	}

	/**
	 * @param \WP_Post|int $id
	 *
	 * @return CommunicationList
	 */
	public function get_by_id( $id ) {
		if ( $id instanceof \WP_Post ) {
			$post = $id;
		} else {
			$post = get_post( $id );
		}
		$persistence = new CommunicationListPersistence( $post->ID );

		return new CommunicationList(
			$id,
			$post->post_title,
			$persistence->get( CommunicationListPersistence::FIELD_TYPE_KEY ),
			$persistence->get( CommunicationListPersistence::FIELD_CHECKOUT_AVAILABLE_KEY ) === 'yes',
			$persistence->get( CommunicationListPersistence::FIELD_CHECKBOX_LABEL_KEY ),
			$persistence->get( CommunicationListPersistence::FIELD_CHECKBOX_DESCRIPTION_KEY )
		);
	}

	/**
	 * @param \WP_Post|int $id
	 *
	 * @return CommunicationListForTable
	 * @deprecated 2.37 Implementation relies on deprecated package.
	 */
	public function get_by_id_for_table( $id ) {
		if ( $id instanceof \WP_Post ) {
			$post = $id;
		} else {
			$post = get_post( $id );
		}
		$persistence = new CommunicationListPersistence( $post->ID );

		return new CommunicationListForTable(
			$persistence->get( CommunicationListPersistence::FIELD_TYPE_KEY ),
			$this->get_optins( $post->ID ),
			$this->get_optouts( $post->ID )
		);
	}

	/**
	 * @param int $type_id
	 *
	 * @return int
	 */
	private function get_optins( $type_id ) {
		$optin_table = DatabaseSchema::get_optin_email_table_name();
		$sql         = "SELECT COUNT(*) FROM {$optin_table} WHERE communication_type = %d AND active = '1' AND subscribe = '1'";

		return (int) $this->wpdb->get_var( $this->wpdb->prepare( $sql, $type_id ) );
	}

	/**
	 * @param int $type_id
	 *
	 * @return int
	 */
	private function get_optouts( $type_id ) {
		$optin_table = DatabaseSchema::get_optin_email_table_name();
		$sql         = "SELECT COUNT(*) FROM {$optin_table} WHERE communication_type = %d AND active = '1' AND subscribe = '0'";

		return (int) $this->wpdb->get_var( $this->wpdb->prepare( $sql, $type_id ) );
	}

	/**
	 * @return CommunicationList[]
	 */
	public function get_all() {
		$posts = self::get_lists_as_posts();
		$types = [];
		foreach ( $posts as $post ) {
			$types[] = $this->get_by_id( $post->ID );
		}

		return $types;
	}

	/**
	 * @deprecated Use CommunicationListRepository::get_email_optin_lists_ids()
	 */
	public static function get_email_lists_ids( string $email ): array {
		return self::get_email_optin_lists_ids( $email );
	}

	/** @deprecated 2.37 Relies on deprecated package. */
	public static function get_email_optin_lists_ids( string $email ): array {
		$email_list_type = self::get_email_opt_model( $email )->get_optins();

		return array_map(
			static function ( $optin ) {
				return $optin->get_list_id();
			},
			$email_list_type
		);
	}

	/** @deprecated 2.37 Relies on deprecated package. */
	public static function get_email_optout_lists_ids( string $email ): array {
		$email_list_type = self::get_email_opt_model( $email )->get_optouts();

		return array_map(
			static function ( $optouts ) {
				return $optouts->get_list_id();
			},
			$email_list_type
		);
	}

	/**
	 * @global \wpdb
	 */
	private static function get_email_opt_model( string $email ): EmailOptModel {
		global $wpdb;
		$repository = new EmailOptRepository( $wpdb );
		return $repository->find_by_email( $email );
	}

	/**
	 * @return \WP_Post[]
	 * @internal Only for use in CommunicationListRespository
	 */
	public static function get_lists_as_posts() {
		return get_posts(
			[
				'post_type'   => CommunicationListPostType::TYPE,
				'numberposts' => - 1,
			]
		);
	}

	/**
	 * @return string[] Indexed by id.
	 */
	public static function get_lists_as_select_options() {
		$posts   = self::get_lists_as_posts();
		$options = [];
		foreach ( $posts as $post ) {
			/** @var \WP_Post $post */
			$options[ $post->ID ] = $post->post_title;
		}

		return $options;
	}
}
