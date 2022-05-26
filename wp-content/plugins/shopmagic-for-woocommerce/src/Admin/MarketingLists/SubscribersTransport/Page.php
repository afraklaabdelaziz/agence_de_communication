<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\Admin\MarketingLists\SubscribersTransport;

use ShopMagicVendor\WPDesk\Forms\Field\NoOnceField;
use ShopMagicVendor\WPDesk\Forms\Field\SelectField;
use ShopMagicVendor\WPDesk\Forms\Field\SubmitField;
use ShopMagicVendor\WPDesk\Forms\Form\FormWithFields;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\ShopMagic\Admin\Form\Fields\FileInput;
use WPDesk\ShopMagic\Admin\Form\Fields\TableHeader;
use WPDesk\ShopMagic\Admin\TableList\AdminPage;
use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Helper\CapabilitiesCheckTrait;

final class Page implements AdminPage {
	use CapabilitiesCheckTrait;

	/** @var Renderer */
	private $renderer;

	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/** @return void */
	public function register() {
		$allowed_capability = $this->allowed_capability();
		if ( $allowed_capability ) {
			add_submenu_page(
				'',
				esc_html__( 'Import/Export', 'shopmagic-for-woocommerce' ),
				esc_html__( 'Import/Export', 'shopmagic-for-woocommerce' ),
				$allowed_capability,
				'import-export',
				[ $this, 'render' ]
			);
		}
	}

	private function get_import_form(): FormWithFields {
		return new FormWithFields(
			[
				( new TableHeader() )
					->set_label( esc_html__( 'Import', 'shopmagic-for-woocommerce' ) ),
				( new NoOnceField( Process::IMPORT_ACTION ) )
					->set_name( 'nonce' ),
				( new FileInput() )
					->set_attribute( 'accept', 'text/csv' )
					->set_label( esc_html__( 'Import file', 'shopmagic-for-woocommerce' ) )
					->set_required()
					->set_name( 'file_input' ),
				( new SelectField() )
					->set_label( 'Choose list' )
					->set_name( 'list_id' )
					->set_required()
					->set_options( CommunicationListRepository::get_lists_as_select_options() ),
				( new SubmitField() )
					->set_name( 'submit' )
					->add_class( 'button-primary' )
					->set_label( esc_html__( 'Import', 'shopmagic-for-woocommerce' ) ),
			],
			'import'
		);
	}

	private function get_export_form(): FormWithFields {
		return new FormWithFields(
			[
				( new TableHeader() )
					->set_label( esc_html__( 'Export', 'shopmagic-for-woocommerce' ) ),
				( new NoOnceField( Process::EXPORT_ACTION ) )
					->set_name( 'nonce' ),
				( new SelectField() )
					->set_label( 'Choose list' )
					->set_name( 'list_id' )
					->set_required()
					->set_options( CommunicationListRepository::get_lists_as_select_options() ),
				( new SubmitField() )
					->set_name( 'submit' )
					->add_class( 'button-primary' )
					->set_label( esc_html__( 'Export', 'shopmagic-for-woocommerce' ) ),

			],
			'export'
		);
	}

	/**
	 * @internal
	 * @return void
	 */
	public function render() {
		$this->renderer->output_render(
			'import_export',
			[
				'import_form' => $this->get_import_form(),
				'export_form' => $this->get_export_form(),
				'renderer'    => $this->renderer,
			]
		);
	}
}
