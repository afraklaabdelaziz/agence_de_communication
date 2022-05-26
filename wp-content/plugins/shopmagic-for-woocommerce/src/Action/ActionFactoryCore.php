<?php

namespace WPDesk\ShopMagic\Action;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use ShopMagicVendor\WPDesk\Logger\LoggerFactory;
use ShopMagicVendor\WPDesk\Persistence\Adapter\ArrayContainer;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\ShopMagic\Action\Builtin\OptinList\AddToListAction;
use WPDesk\ShopMagic\Action\Builtin\OptinList\DeleteFromListAction;
use WPDesk\ShopMagic\Action\Builtin\SendMail\SendMailAction;
use WPDesk\ShopMagic\Action\Builtin\SendMail\SendPlainTextMailAction;
use WPDesk\ShopMagic\Action\Builtin\SendMail\SendRawHTMLMailAction;
use WPDesk\ShopMagic\DataSharing\TestDataProvider;
use WPDesk\ShopMagic\Integration\Mailchimp\AddToMailChimpListAction;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;
use WPDesk\ShopMagic\Placeholder\PlaceholderProcessor;

/**
 * Factory for actions. When you need an action instance you should use this class.
 *
 * @package WPDesk\ShopMagic\Action
 */
final class ActionFactoryCore implements ActionFactory2 {
	/**
	 * Return an action corresponding to given slug.
	 * This action can be executed. When you only want to list possible actions use @param ContainerInterface $data Fields values.
	 *
	 * @param string slug Unique action slug.
	 * @param ContainerInterface $data Data from provider to hydrate action.
	 *
	 * @return Action
	 * @see get_action.
	 * Actions are implemented using Prototype Pattern.
	 */
	public function create_action( string $slug, ContainerInterface $data ): Action {
		$action = clone $this->get_action( $slug );
		$action->update_fields_data( $data );

		return $action;
	}

	/**
	 * Return an action corresponding to given slug.
	 * WARNING: Returned action is not hydrated with field data so should never be executed.
	 * If you want to execute an action. @return Action When action is not found returns null object
	 *
	 * @see create_action
	 * This method can be used for testing action execution but fields_data must be hydrated manually.
	 */
	public function get_action( string $slug ): Action {
		$action_list = $this->get_action_list();
		if ( isset( $action_list[ $slug ] ) ) {
			return apply_filters( 'shopmagic/core/single_action', $action_list[ $slug ] );
		}

		return new NullAction();
	}

	/**
	 * Returns all actions.
	 *
	 * @return Action[] In format [ slug => instance, .. ]
	 */
	public function get_action_list(): array {
		return apply_filters( 'shopmagic/core/actions', $this->get_build_in_actions() );
	}

	/**
	 * Returns actions that are built in core.
	 *
	 * @return Action[] In format [ slug => instance, .. ]
	 */
	private function get_build_in_actions() {
		return [
			'shopmagic_sendemail_action'          => new SendMailAction(),
			'shopmagic_plain_text_email_action'   => new SendPlainTextMailAction(),
			'shopmagic_raw_html_email_action'     => new SendRawHTMLMailAction(),
			'shopmagic_addtomailchimplist_action' => new AddToMailChimpListAction(),
			'shopmagic_add_to_list_action'        => new AddToListAction(),
			'shopmagic_delete_from_list_action'   => new DeleteFromListAction(),
		];
	}

	/**
	 * Initialize all action by injecting test data and running hooks if has any.
	 *
	 * @param LoggerInterface $logger
	 */
	public function initialize_actions_additional_hooks( LoggerInterface $logger ) {
		array_map(
			static function ( Action $action ) use ( $logger ) {
				if ( $action instanceof LoggerAwareInterface ) {
					$action->setLogger( $logger );
				}
				if ( method_exists( $action, 'hooks' ) ) {
					$action->hooks();
				}
			},
			$this->get_action_list()
		);
	}
}
