<?php


namespace WPDesk\ShopMagic\Action\Builtin\OptinList;

use WPDesk\ShopMagic\Action\BasicAction;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\FormField\Field\InputTextField;
use WPDesk\ShopMagic\FormField\Field\SelectField;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\Optin\EmailOptRepository;

/**
 * Abstract template for Lists related actions.
 */
abstract class AbstractListAction extends BasicAction {
	const PARAM_LIST  = 'list';
	const PARAM_EMAIL = 'email';

	/** @var ListTable */
	protected $table;

	/** @var ListFactory */
	protected $factory;

	final public function __construct( ListTable $table = null, ListFactory $factory = null ) {
		$this->factory = $factory ?? new ListFactory();
		$this->table   = $table ?? new ListTable( $this->factory );
	}

	final public function execute( Automation $automation, Event $event ): bool {
		$email = $this->get_email();
		if ( ! empty( $email ) ) {
			$list_id   = absint( $this->fields_data->get( self::PARAM_LIST ) );
			$list_name = get_the_title( $list_id );

			return $this->do_list_action( $email, $list_id, $list_name );
		}

		return false;
	}

	private function get_email(): string {
		return $this->placeholder_processor->process( $this->fields_data->get( self::PARAM_EMAIL ) );
	}

	abstract protected function do_list_action( string $email, int $list_id, string $list_name ): bool;

	final public function get_required_data_domains(): array {
		return [];
	}

	public function get_fields(): array {
		return [
			( new SelectField() )
				->set_label( esc_html__( 'List', 'shopmagic-for-woocommerce' ) )
				->set_options( CommunicationListRepository::get_lists_as_select_options() )
				->set_name( self::PARAM_LIST ),
			( new InputTextField() )
				->set_label( esc_html__( 'Email', 'shopmagic-for-woocommerce' ) )
				->set_placeholder( esc_html__( 'E-mail or a placeholder with an e-mail', 'shopmagic-for-woocommerce' ) )
				->set_name( self::PARAM_EMAIL ),
		];
	}
}
