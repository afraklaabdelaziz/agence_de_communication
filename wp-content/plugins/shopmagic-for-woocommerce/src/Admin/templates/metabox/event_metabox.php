<?php
/**
 * @var \WPDesk\ShopMagic\Event\Event[] $events
 * @var string $event_slug
 * @var \WPDesk\ShopMagic\Event\EventFactory2 $event_factory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="_shopmagic_edit_page"></div>
<table class="shopmagic-table">
	<tbody>
	<tr class="shopmagic-field">
		<td class="shopmagic-label">
			<label for="_event"><?php esc_html_e( 'Event', 'shopmagic-for-woocommerce' ); ?></label>

			<div id="event-desc-area">
				<p class="content"></p>
			</div>
		</td>

		<td class="shopmagic-input">
			<select name="_event" id="_event" title="<?php esc_html_e( 'Event', 'shopmagic-for-woocommerce' ); ?>">
				<option value="" <?php selected( '', $event_slug ); ?>>
					<?php esc_html_e( 'Select...', 'shopmagic-for-woocommerce' ); ?>
				</option>
				<?php

				// Order all events by group.
				uasort(
					$events,
					/**
					 * Compares events by groups object.
					 *
					 * @param \WPDesk\ShopMagic\Event\Event $a
					 * @param \WPDesk\ShopMagic\Event\Event $b
					 *
					 * @return int compare result
					 */
					function ( $a, $b ) {
						return strcmp( $a->get_group_slug(), $b->get_group_slug() );
					}
				);

				$prev_group = '';
				foreach ( $events as $slug => $event ) {
					if ( $prev_group !== $event->get_group_slug() ) { // Group was changed.
						if ( $prev_group !== '' ) {
							echo '</optgroup>';
						}
						?>

						<optgroup label="<?php echo esc_attr( $event_factory->event_group_name( $event->get_group_slug() ) ); ?>">

						<?php
						$prev_group = $event->get_group_slug();
					}
					?>

					<option value="<?php echo esc_attr( $slug ); ?>" <?php echo selected( $slug, $event_slug, false ); ?>><?php echo esc_html( $event->get_name() ); ?></option>

					<?php
				}
				?>
				</optgroup>
			</select>

			<div class="error-icon">
				<span class="dashicons dashicons-warning"></span>
				<div class="error-icon-tooltip">Network connection error</div>
			</div>
			<div class="spinner"></div>
		</td>
	</tr>
	</tbody>
</table>
<table id="event-config-area" class="shopmagic-table"></table>
