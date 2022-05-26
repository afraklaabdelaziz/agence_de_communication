<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Form\FormWithFields $import_form
 * @var \ShopMagicVendor\WPDesk\Forms\Form\FormWithFields $export_form
 * @var \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer
 */

use WPDesk\ShopMagic\Admin\MarketingLists\SubscribersTransport\Process;

?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Here you can import or export subscribers with CSV files', 'shopmagic-for-woocommerce' ); ?></h1>

	<form enctype="multipart/form-data"
		  id="<?php echo esc_attr( $import_form->get_form_id() ); ?>"
		  action="<?php echo esc_attr( $import_form->get_action() ); ?>"
		  method="<?php echo esc_attr( $import_form->get_method() ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( Process::IMPORT_ACTION); ?>"/>
	<table>
		<tbody>
		<?php echo $import_form->render_fields( $renderer ); ?>
		</tbody>
	</table>
	</form>
	<form id="<?php echo esc_attr( $export_form->get_form_id() ); ?>"
		  enctype="application/x-www-form-urlencoded"
		  action="<?php echo esc_attr( $export_form->get_action() ); ?>"
		  method="<?php echo esc_attr( $export_form->get_method() ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( Process::EXPORT_ACTION); ?>">
	<table>
		<tbody>
		<?php echo $export_form->render_fields( $renderer ); ?>
		</tbody>
	</table>
	</form>
</div>
