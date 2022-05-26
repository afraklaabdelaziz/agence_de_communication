<?php
/**
 * @var \WPDesk\ShopMagic\Admin\TableList\AbstractTableList $table
 * @var string $title
 * @var string $action_url
 * @var string $post_type
 * @var string $page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( $title ); ?>
	</h1>

	<form method="GET" id="mainform" action="<?php echo esc_url( $action_url ); ?>">
		<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
		<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
		<?php $table->search_box( esc_html__( 'Search for email', 'shopmagic-for-woocommerce' ), 'email' ); ?>

		<?php $table->display(); ?>
	</form>
</div>
