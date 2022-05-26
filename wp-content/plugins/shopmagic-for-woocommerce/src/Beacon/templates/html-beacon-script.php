<?php
/**
 * Displays Beacon script.
 *
 * @var $beacon_id string .
 */

$beacon_button_class = 'wpdesk-helpscout-beacon-button';

?>
<div id="wpdesk-helpscout-beacon">
	<div class="wpdesk-helpscout-beacon-frame">
		<button class="<?php echo esc_attr( $beacon_button_class ); ?>">
			<span class="icon">
				<svg width="24" height="22" xmlns="http://www.w3.org/2000/svg"><path d="M20.347 20.871l-.003-.05c0 .017.001.034.003.05zm-.243-4.278a2 2 0 0 1 .513-1.455c1.11-1.226 1.383-2.212 1.383-4.74C22 5.782 18.046 2 13.125 2h-2.25C5.954 2 2 5.78 2 10.399c0 4.675 4.01 8.626 8.875 8.626h2.25c.834 0 1.606-.207 3.212-.798a2 2 0 0 1 1.575.083l2.355 1.161-.163-2.878zM10.875 0h2.25C19.13 0 24 4.656 24 10.399c0 2.6-.25 4.257-1.9 6.08l.243 4.279c.072.845-.807 1.471-1.633 1.162l-3.682-1.816c-1.212.446-2.527.921-3.903.921h-2.25C4.869 21.025 0 16.142 0 10.4 0 4.656 4.869 0 10.875 0z" fill="#FFF"></path></svg>
			</span>

			<span class="text"><?php esc_html_e( 'Help', 'shopmagic-for-woocommerce' ); ?></span>
		</button>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function () {
		(new HsBeacon(
			'<?php echo esc_attr( $beacon_id ); ?>',
			'<?php echo esc_attr( __( 'When you click OK we will open our HelpScout beacon where you can find answers to your questions. This beacon will load our help articles and also potentially set cookies.', 'shopmagic-for-woocommerce' ) ); ?>'
		)).attachBeaconEvents('<?php echo esc_attr( $beacon_button_class ); ?>');
	});
</script>
