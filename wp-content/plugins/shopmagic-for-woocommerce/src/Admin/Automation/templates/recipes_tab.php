<?php
/**
 * @var \WPDesk\ShopMagic\Recipe\Recipe[] $recipes
 */

defined( 'ABSPATH' ) || exit;
?>

<h1 class="wp-heading-inline"><?php esc_html_e( 'Recipes', 'shopmagic-for-woocommerce' ); ?></h1>

<table class="wp-list-table widefat striped">
	<thead>
	<tr>
		<th><?php esc_html_e( 'Recipe', 'shopmagic-for-woocommerce' ); ?></th>
		<th><?php esc_html_e( 'Actions', 'shopmagic-for-woocommerce' ); ?></th>
	</tr>
	</thead>
	<?php foreach ( $recipes as $recipe ) : ?>
		<tr>
			<td>
				<p><span class="row-title"><?php echo esc_html( $recipe->get_name() ); ?></span></p>
				<p><?php echo esc_html( $recipe->get_description() ); ?></p>
			</td>
			<td style="vertical-align: middle;">
			<?php if ( $recipe->can_use() ) : ?>
				<a class="button button-primary" href="
				<?php
				echo esc_url(
					add_query_arg(
						[
							'action' => 'shopmagic_brew_recipe',
							'recipe' => $recipe->get_id(),
						],
						admin_url( 'admin-ajax.php' )
					)
				);
				?>
					"><?php esc_html_e( 'Use recipe', 'shopmagic-for-woocommerce' ); ?></a>
			<?php else : ?>
				<a class="button" href="https://shopmagic.app/blog/shopmagic-2-23-is-here/?utm_source=recipes&utm_medium=link&utm_campaign=shopmagic-notice&utm_content=learn-more" target="_blank"><?php esc_html_e( 'Learn more', 'shopmagic-for-woocommerce' ); ?></a>
			<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
