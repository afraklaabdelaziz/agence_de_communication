<?php
/**
 * @var string[] $available_types
 */

use WPDesk\ShopMagic\CommunicationList\CommunicationList;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	</tbody>
</table>

<script type="text/javascript">
	(function ($) {
		let $type = $('#type');
		let $checkout = $('#checkout_available');
		let $checkoutSettings = $checkout.parents('.shopmagic-field');

		function updateVisibility() {
			let $typeValue = $type.val();

			// for available types check CommunicationType class
			if ( $typeValue === '<?php echo CommunicationList::TYPE_OPTOUT; ?>' ) {
				$checkoutSettings.hide();
				$checkout.prop( 'checked', false );
			} else {
				$checkoutSettings.show();
			}
		}

		$type.change(function () {
			updateVisibility();
		});

		updateVisibility();

	})(jQuery);
</script>
