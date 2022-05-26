<?php

namespace WPDesk\ShopMagic;

use ShopMagicVendor\WPDesk\Forms\Form\FormWithFields;
use ShopMagicVendor\WPDesk\Forms\Persistence\FieldPersistenceStrategy;
use ShopMagicVendor\WPDesk\Persistence\PersistentContainer;
use WPDesk\ShopMagic\Admin\Automation\AutomationFormFields\AutomationFieldRenderer;

/**
 * @deprecated
 */
final class FormIntegration {

	public function get_renderer() {
		return new AutomationFieldRenderer();
	}

	private function get_form_processor( PersistentContainer $persistent_container ) {
		return new FieldPersistenceStrategy( $persistent_container );
	}

	/**
	 * @param PersistentContainer $persistent_container
	 * @param FormWithFields $form
	 *
	 * @return FormWithFields
	 */
	public function load_form( PersistentContainer $persistent_container, FormWithFields $form ) {
		$form->handle_request( $this->get_form_processor( $persistent_container )->load_fields( $form ) );

		return $form;
	}

	/**
	 * @param PersistentContainer $persistent_container
	 * @param FormWithFields $form
	 */
	public function persists_form( PersistentContainer $persistent_container, FormWithFields $form ) {
		$this->get_form_processor( $persistent_container )->persist_fields( $form, $form->get_data() );
	}
}

