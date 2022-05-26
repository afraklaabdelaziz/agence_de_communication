<?php

namespace WPDesk\ShopMagic\Admin\CommunicationList;

use ShopMagicVendor\WPDesk\Forms\Field\SelectField;
use ShopMagicVendor\WPDesk\Forms\Field\TextAreaField;
use ShopMagicVendor\WPDesk\Forms\FieldProvider;
use ShopMagicVendor\WPDesk\Forms\Form\FormWithFields;
use ShopMagicVendor\WPDesk\Forms\Resolver\DefaultFormFieldResolver;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\ChainResolver;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use ShopMagicVendor\WPDesk\View\Resolver\Resolver;
use WPDesk\ShopMagic\CommunicationList\CommunicationList;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPersistence;
use WPDesk\ShopMagic\CommunicationList\CommunicationListPostType;
use WPDesk\ShopMagic\FormField\Field\CheckboxField;
use WPDesk\ShopMagic\FormField\Field\InputTextField;

/**
 * Admin save/load custom fields for Communication Type add/edit form.
 *
 * @package WPDesk\ShopMagic\Admin\CommunicationType
 */
final class CommunicationListSettingsMetabox implements FieldProvider {
	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [
			( new SelectField() )
				->set_name( CommunicationListPersistence::FIELD_TYPE_KEY )
				->set_label( __( 'List type', 'shopmagic-for-woocommerce' ) )
				->set_options(
					[
						CommunicationList::TYPE_OPTIN  => __( 'Opt-in', 'shopmagic-for-woocommerce' ),
						CommunicationList::TYPE_OPTOUT => __( 'Opt-out', 'shopmagic-for-woocommerce' ),
					]
				)
				->set_description(
					esc_html__( 'Opt-in communication requires customer consent. Opt-out communication (not recommended) is sent until the customer opts out.', 'shopmagic-for-woocommerce' ) .
					sprintf( ' <a href="https://docs.shopmagic.app/" target="_blank">%s</a> &rarr;', esc_html__( 'Learn more', 'shopmagic-for-woocommerce' ) )
				),
			( new CheckboxField() )
				->set_name( CommunicationListPersistence::FIELD_CHECKOUT_AVAILABLE_KEY )
				->set_label( __( 'Opt-in checkbox', 'shopmagic-for-woocommerce' ) )
				->set_sublabel( __( 'Show in checkout', 'shopmagic-for-woocommerce' ) )
				->set_description_tip( __( 'You may choose to show the checkbox in checkout.', 'shopmagic-for-woocommerce' ) ),
			( new InputTextField() )
				->set_name( CommunicationListPersistence::FIELD_CHECKBOX_LABEL_KEY )
				->set_label( __( 'Checkbox label', 'shopmagic-for-woocommerce' ) )
				->set_description_tip( __( 'The checkbox will always be available in the Communication preferences page to let the customers opt-out.', 'shopmagic-for-woocommerce' ) )
				->set_required(),
			( new TextAreaField() )
				->set_name( CommunicationListPersistence::FIELD_CHECKBOX_DESCRIPTION_KEY )
				->set_label( __( 'Checkbox description', 'shopmagic-for-woocommerce' ) ),

		];
	}

	/**
	 * @return Resolver
	 */
	private function get_add_resolver() {
		return new ChainResolver(
			new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' ),
			new DefaultFormFieldResolver()
		);
	}

	/**
	 * @param int $post_id List id.
	 *
	 * @return FormWithFields
	 */
	private function get_form( $post_id ) {
		$form = new FormWithFields( $this->get_fields() );
		$form->set_data( new CommunicationListPersistence( $post_id ) );

		return $form;
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @internal
	 */
	public function render_list_fields( $post ) {
		$renderer = new SimplePhpRenderer( $this->get_add_resolver() );

		echo $renderer->render( 'form-start' );
		echo $this->get_form( $post->ID )->render_fields( $renderer );
		echo $renderer->render( 'form-end' );
	}

	/**
	 * @param int $post_id List id.
	 */
	public function save_fields( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$form = $this->get_form( $post_id ); // TODO: template method pattern.
		$form->handle_request( $_POST[ $form->get_form_id() ] );
		if ( $form->is_submitted() && $form->is_valid() ) {
			$form->put_data( new CommunicationListPersistence( $post_id ) );
		}
	}

	public function hooks() {
		add_meta_box(
			'shopmagic_list_settings_metabox',
			__( 'Settings', 'shopmagic-for-woocommerce' ),
			[
				$this,
				'render_list_fields',
			],
			CommunicationListPostType::TYPE,
			'normal'
		);
		add_action( 'save_post_' . CommunicationListPostType::TYPE, [ $this, 'save_fields' ] );
	}
}
