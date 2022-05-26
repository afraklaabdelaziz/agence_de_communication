<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\MarketingLists\SubscribersTransport;

use ShopMagicVendor\League\Csv\CannotInsertRecord;
use ShopMagicVendor\League\Csv\Reader;
use ShopMagicVendor\League\Csv\Statement;
use ShopMagicVendor\League\Csv\Writer;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Exception\CannotProvideItemException;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Guest\GuestFactory;
use WPDesk\ShopMagic\LoggerFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListDTO;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;

final class Process {
	const ACCEPT_EXTENSION = 'csv';
	const IMPORT_ACTION    = 'shopmagic_import_list';
	const EXPORT_ACTION    = 'shopmagic_export_list';
	const BATCH_LIMIT      = 200;

	/** @var ListTable */
	private $list_table;


	public function __construct( ListTable $list_table ) {
		$this->list_table = $list_table;
	}

	/** @return void */
	public function hooks() {
		add_action( 'wp_ajax_' . self::IMPORT_ACTION, [ $this, 'process_import' ] );
		add_action( 'wp_ajax_' . self::EXPORT_ACTION, [ $this, 'process_export' ] );
	}

	/** @return void */
	public function process_import() {
		$nonce  = isset( $_POST['import']['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['import']['nonce'] ) ) : '';
		$result = wp_verify_nonce( $nonce, self::IMPORT_ACTION );
		if ( $result === false ) {
			wp_die( -1, 403 );
		}

		$file_input = isset( $_FILES['import']['tmp_name']['file_input'] ) ? wp_unslash( $_FILES['import']['tmp_name']['file_input'] ) : '';
		$this->validate_file( $file_input );

		$reader = Reader::createFromPath( $file_input, 'r' );

		$run    = isset( $_POST['run'] ) ? absint( $_POST['run'] ) : 0;
		$offset = $run * self::BATCH_LIMIT;
		if ( $this->runs_needed( $reader ) === $run ) {
			wp_send_json_success( [ 'context' => sprintf( 'Successfully imported %d contacts.', $reader->count() ) ] );
		}

		$statement = ( new Statement() )->offset( $offset )->limit( self::BATCH_LIMIT );

		global $wpdb;
		$customer_dao     = new CustomerDAO();
		$customer_factory = new CustomerFactory();
		$list_factory     = new ListFactory();
		$repository       = new GuestDAO( $wpdb );
		$guest_factory    = new GuestFactory( $repository );

		foreach ( $statement->process( $reader )->fetchColumn() as $email ) {
			if ( ! is_email( $email ) ) {
				continue;
			}

			try {
				$customer = $customer_dao->find_by_email( $email );
			} catch ( CustomerNotFound $e ) {
				$guest = $guest_factory->create_from_email_and_db( $email );
				$repository->save( $guest );
				$customer = $customer_factory->create_from_guest( $guest );
			}

			$list_id = isset( $_POST['import']['list_id'] ) ? absint( wp_unslash( $_POST['import']['list_id'] ) ) : 0;
			if ( $this->list_table->is_subscribed_to_list( $customer->get_email(), $list_id ) ) {
				continue;
			}

			try {
				/** @var \WPDesk\ShopMagic\MarketingLists\DAO\ListDTO $customer_status */
				$customer_status = $this->list_table->get_subscribed_to_list( $customer->get_email(), $list_id );
			} catch ( CannotProvideItemException $e ) {
				/** @var \WPDesk\ShopMagic\MarketingLists\DAO\ListDTO $customer_status */
				$customer_status = $list_factory->create_for_email_and_list( $customer->get_email(), $list_id );
			}

			$customer_status->set_active( true );
			$this->list_table->save( $customer_status );
		}

		wp_send_json_success( [ 'run' => ++$run ] );
	}

	/**
	 * @internal
	 * @return void
	 */
	public function process_export() {
		$nonce  = isset( $_POST['export']['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['export']['nonce'] ) ) : '';
		$result = wp_verify_nonce( $nonce, self::EXPORT_ACTION );
		if ( $result === false ) {
			wp_die( -1, 403 );
		}

		$list_id = isset( $_POST['export']['list_id'] ) ? absint( wp_unslash( $_POST['export']['list_id'] ) ) : 0;

		$wp_upload_dir = wp_upload_dir();
		$writer        = Writer::createFromPath( trailingslashit( $wp_upload_dir['path'] ) . $this->get_filename(), 'a+' );

		$run    = isset( $_POST['run'] ) ? absint( $_POST['run'] ) : 0;
		$offset = $run * self::BATCH_LIMIT;
		$count  = (int) ceil( $this->list_table->get_count( [ 'list_id' => $list_id ] ) / self::BATCH_LIMIT );
		if ( $count === $run ) {
			wp_send_json_success(
				[
					'context'  => esc_html__( 'Export finished', 'shopmagic-for-woocommerce' ),
					'download' => esc_url_raw( trailingslashit( $wp_upload_dir['url'] ) . $this->get_filename() ),
				]
			);
		}

		$list_subscribers = $this->list_table->get_all( [ 'list_id' => $list_id ], [], $offset, self::BATCH_LIMIT );

		/** @var ListDTO $entry */
		foreach ( $list_subscribers as $entry ) {
			try {
				$writer->insertOne( [ $entry->get_email() ] );
			} catch ( CannotInsertRecord $e ) {
				LoggerFactory::get_logger()->warning( 'Cannot insert entry' );
			}
		}

		wp_send_json_success( [ 'run' => ++$run ] );
	}

	private function runs_needed( Reader $reader ): int {
		$total_records = $reader->count();
		return (int) ceil( $total_records / self::BATCH_LIMIT );
	}

	private function get_filename(): string {
		return 'shopmagic-subscribers-' . gmdate( 'Y-m-d' ) . '.csv';
	}

	/** @return void */
	private function validate_file( string $file_input ) {
		if ( empty( $file_input ) ) {
			wp_send_json_error( [ 'context' => esc_html__( 'File not submitted', 'shopmagic-for-woocommerce' ) ] );
		}

		if ( ! file_exists( $file_input ) ) {
			wp_send_json_error( [ 'context' => esc_html__( 'An error occurred during file submission.', 'shopmagic-for-woocommerce' ) ] );
		}
	}

}
