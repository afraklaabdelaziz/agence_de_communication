<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var string $slug
 * @var \WPDesk\ShopMagic\Placeholder\Placeholder $placeholder
 */
?>
<div id="dialog" title="<?php echo esc_attr( $placeholder->get_slug() ); ?>">
	<?php
		$description = $placeholder->get_description();

	if ( $description ) :
		?>
		<p class="dialog-description"><?php echo wp_kses_post( $description ); ?></p>
	<?php endif; ?>

	<table class="shopmagic-table">
		<tbody>
