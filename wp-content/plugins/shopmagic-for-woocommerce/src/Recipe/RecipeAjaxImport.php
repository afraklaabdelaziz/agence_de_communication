<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Recipe;

use WPDesk\ShopMagic\Automation\AutomationElementLists;

/**
 * Import automations from JSON from AJAX request.
 */
final class RecipeAjaxImport {
	/** @var int Error code set when inserting automation failed. */
	const AUTOMATION_IMPORT_ERROR = 0;
	/** @var int Error code set when automation cannot handle imported recipe. */
	const MISSING_AUTOMATION_ELEMENTS = - 1;

	/** @var AutomationElementLists */
	private $dependencies;

	public function __construct( AutomationElementLists $dependencies ) {
		$this->dependencies = $dependencies;
	}

	/** @return void */
	public function hooks() {
		add_action( 'wp_ajax_shopmagic_import_automation', [ $this, 'import_automation' ] );
	}

	/** @return void */
	public function import_automation() {
		check_ajax_referer( 'shopmagic-automation-import', 'security' );
		$file = ! empty( $_FILES['automations-json'] ) ? $_FILES['automations-json'] : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$this->validate_file( $file );

		$file_data = json_decode( file_get_contents( $file['tmp_name'] ), true ); // @phpstan-ignore-line
		$result    = $this->import_recipes( $file_data );

		if ( array_intersect( [ self::AUTOMATION_IMPORT_ERROR, self::MISSING_AUTOMATION_ELEMENTS ], $result ) ) {
			$this->send_error_message( $result );
		}

		$this->send_success_message( $result );
	}

	/**
	 * @param array{tmp_name: string, error: int} $file
	 *
	 * @return void
	 */
	private function validate_file( array $file ) {
		if ( ! is_uploaded_file( $file['tmp_name'] ) ||
			$file['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( esc_html__( 'File upload error', 'shopmagic-for-woocommerce' ) );
		}
	}

	/**
	 * @param array{int|string|array{int|string}} $file_data
	 *
	 * @return int[]
	 */
	private function import_recipes( array $file_data ): array {
		if ( $this->array_is_sequential( $file_data ) ) {
			$result = [];
			foreach ( $file_data as $recipe ) {
				/** @var array{name:string,description:string,actions:array,event:array,filters:array} $recipe */
				$result[] = $this->import_single_recipe( $recipe );
			}
			return $result;
		}

		/** @var array{name:string,description:string,actions:array,event:array,filters:array} $file_data */
		return (array) $this->import_single_recipe( $file_data );
	}

	/**
	 * @param array{name:string,description:string,actions:array,event:array,filters:array} $recipe_data
	 *
	 * @return int Returns ID of inserted post on success, 0 when insertion failed or -1 when cannot insert.
	 */
	private function import_single_recipe( array $recipe_data ): int {
		$recipe = new Recipe( $recipe_data, 'id', $this->dependencies );
		if ( $recipe->can_use() ) {
			return $recipe->import();
		}
		return self::MISSING_AUTOMATION_ELEMENTS;
	}

	// @phpstan-ignore-next-line
	private function array_is_sequential( array $file_data ): bool {
		return array_keys( $file_data ) === range( 0, count( $file_data ) - 1 );
	}

	/**
	 * @param int[] $result
	 *
	 * @return void
	 */
	private function send_error_message( array $result ) {
		if ( in_array( self::MISSING_AUTOMATION_ELEMENTS, $result, true ) ) {
			wp_send_json_error( esc_html__( 'You do not have all required addons installed to import the automation.', 'shopmagic-for-woocommerce' ) );
		} else {
			$errors_count = count(
				array_filter(
					$result,
					static function ( $i ) {
						return $i === self::AUTOMATION_IMPORT_ERROR;
					}
				)
			);
			wp_send_json_error(
				sprintf(
				// translators: %d count of errors in imported automations.
					esc_html( _n( 'Error occurred in %d imported automation', 'Error occurred in %d imported automations', $errors_count, 'shopmagic-for-woocommerce' ) ),
					$errors_count
				)
			);
		}
	}

	/**
	 * @param int[] $result
	 *
	 * @return void
	 */
	private function send_success_message( array $result ) {
		$result_count = count( $result );
		wp_send_json_success(
			sprintf(
			// translators: %d count of imported automations.
				esc_html( _n( 'Successfully imported %d automation', 'Successfully imported %d automations', $result_count, 'shopmagic-for-woocommerce' ) ),
				$result_count
			)
		);
	}
}
