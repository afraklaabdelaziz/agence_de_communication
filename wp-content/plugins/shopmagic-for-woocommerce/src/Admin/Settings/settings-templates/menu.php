<div id="shopmagic-settings-header">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php foreach ( $menu_items as $item_key => $item_name ) : ?>
			<a href="<?php echo esc_attr( $base_url ); ?>&tab=<?php echo esc_attr( $item_key ); ?>" class="nav-tab
								<?php
								if ( $selected === $item_key ) :
									?>
				nav-tab-active<?php endif; ?>"><?php echo esc_html( $item_name ); ?></a>
		<?php endforeach; ?>
	</nav>
</div>
