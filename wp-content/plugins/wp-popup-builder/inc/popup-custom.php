<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<input type="hidden" name="popup-url" value="<?php echo WPPB_URL ?>">
<input type="hidden" data-global-save="global-content" value='<?php echo $popupSetData["global_content"]; ?>'>

<div class="wppb-popup-custom <?php echo !$get_CustomPopup ?'rl-display-none':'' ?>" style="background-color:<?php echo $popupSetData['outside-color']; ?>;">
	<div>
	         <?php echo $wp_builder_obj->popup_layout($popupSetData); ?>	
	</div>	
</div> 
