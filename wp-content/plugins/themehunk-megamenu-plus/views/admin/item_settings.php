<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
} 
$menu_item_id = (int) sanitize_text_field( $_POST['menu_item_id'] );
$menu_id = (int) sanitize_text_field( $_POST['menu_id'] );
$menu_item_depth = (int) sanitize_text_field( $_POST['menu_item_depth'] ); 
$themehunk_megamenu_item_megamenu_status = get_post_meta( $menu_item_id, 'themehunk_megamenu_item_megamenu_status', true );
$themehunk_megamenu_layout = get_post_meta( $menu_item_id, 'themehunk_megamenu_layout', true );
$mmth_builder_option  = get_post_meta( $menu_item_id, 'themehunk_megamenu_builder_options', true );
$widgets = ThemeHunk_MegaMenu_Widgets::themehunk_megamenu_get_all_registered_widgets();	
define('THEMEHUNK_MEGAMENU_PANEL_LEFT', THEMEHUNK_MEGAMENU_URL . 'assets/images/panel-left.png');
define('THEMEHUNK_MEGAMENU_PANEL_RIGHT', THEMEHUNK_MEGAMENU_URL . 'assets/images/panel-right.png');
define('THEMEHUNK_MEGAMENU_ALIGN_LEFT', THEMEHUNK_MEGAMENU_URL . 'assets/images/left-align.png');
define('THEMEHUNK_MEGAMENU_ALIGN_CENTER', THEMEHUNK_MEGAMENU_URL . 'assets/images/center-align.png');
define('THEMEHUNK_MEGAMENU_ALIGN_RIGHT', THEMEHUNK_MEGAMENU_URL . 'assets/images/right-align.png');
?>
 <div class="mmth-item-settings-top-bar"> 
 	<div class="mmth-item-settings-title">
        <span class="themehunk-megamenu-item-settings-heading"></span>
    </div>
    <?php if ( $menu_item_depth == 0 ) { ?>
    <span class="themehunk-megamenu-status">
    	<label for='themehunk-megamenu-status-chkbox' class="themehunk-megamenu-status-text <?php echo esc_attr($themehunk_megamenu_item_megamenu_status); ?>"><?php _e('Activate MegaMenu', 'themehunk-megamenu') ?>
    	<input type="checkbox" id="themehunk-megamenu-status-chkbox" <?php checked( $themehunk_megamenu_item_megamenu_status, 'active' ); ?> >
    	</label>
    </span>
	<?php } ?>
	<a href="javascript:;" id="mmth-saving-indicator" style="display: none;"><?php _e('Saving...', 'themehunk-megamenu'); ?></a>
 	<a href="javascript:;" class="themehunk-megamenu-builder-close-btn">
 		<span class="dashicons dashicons-no-alt"></span>
 	</a>
 	<div class="clear"></div>
 </div>
 <div class="mmth-item-settings-builder-area">
 	<div class="mmth-builder-settings-wrapper"> 	
 		<div class="themehunk-megamenu-draggable-widgets-wrapper">
 			<div class=" mmth-builder-settings  themehunk-megamenu-draggable-widgets-list-title active">
 				<i class="dashicons dashicons-editor-kitchensink"></i>   <?php _e('Widgets', 'themehunk-megamenu'); ?>
 			</div>	                          
		    <div class="themehunk-megamenu-draggable-widgets-list">
		    	<?php
		        if ( count( $widgets ) ){
		            foreach ($widgets as $key => $value){
		                echo '<div class="draggable-widget" data-widget-id-base="' . $value['id_base'] . '" data-type="outside-widget"> '. 
		                	'<span class="outside-widget-name">' 
		                	. $value['name'] . 
		                	'</span>'.
		                	' <span class="widgets-drag-btn"><i class="fa fa-arrows"></i>'
		                	.__('Drag', 'themehunk-megamenu').'</span>
		                	</div>';
		            }
		        }
		       ?>
		    </div>
		</div>    
	    <div class="mmth-builder-settings themehunk-megamenu-builder-config-options">
	    	<i class="dashicons dashicons-admin-generic"></i> <?php _e('Options', 'themehunk-megamenu'); ?>
	    </div>	    
	    <div class="mmth-builder-settings themehunk-megamenu-builder-megamenu-icons">
	    	<i class="dashicons dashicons-format-gallery"></i> <?php _e('Icons', 'themehunk-megamenu'); ?>
	    </div>
 	</div>
 	<div class="themehunk-megamenu-builder-content-wrapper">
 		<!-- Please activate MegaMenu for this menu item. -->
 		<?php if ( $menu_item_depth == 0 ) { ?>
 		<h3 class="activate-megamenu-msg" style="<?php if ( $themehunk_megamenu_item_megamenu_status == 'active' ) { echo 'display: none;';	} ?>">
 			<?php _e( 'Please activate MegaMenu for this menu item.', 'themehunk-megamenu' ) ?>	
 		</h3>		
 		<div class="themehunk-megamenu-builder-content" style="<?php if ( $themehunk_megamenu_item_megamenu_status == 'inactive' ) { echo 'display: none;';	} ?>">	
 			<div class="mmth-builder">
				<div class="themehunk-megamenuDraggableWidgetArea">	
			 		<div class="item-widgets-wrap mmth-limit-height">
					   <div id="themehunk_megamenu_item_layout_wrap">
			 			<div id="themehunk_megamenu_item_layout_wrap--notices" style="display: none;"></div>
			            <?php  
	
	                      if ( count($themehunk_megamenu_layout['layout']) ){ 
	                            foreach ($themehunk_megamenu_layout['layout'] as $layout_key => $layout_value){ ?>
	                                <div class="themehunk-megamenu-row" data-row-id="<?php echo $layout_key; ?>">

	                                <div class="themehunk-megamenu-row-actions">
	                                    <div class="themehunk-megamenu-row-left mmthRowSortingIcon"> <i class="fa fa-sort"></i> <?php  _e('Row', 'themehunk-megamenu')  ?></div>
	                                    <div class="themehunk-megamenu-row-right"> 
				 							<span class="themehunk-megamenu-add-col-btn">
	                                    		<span class="dashicons dashicons-plus">
				 								</span>
				 								<?php _e('Column', 'themehunk-megamenu') ?> 
				 							</span>
	                                    	<span class="mmthRowDeleteIcon">
	                                    		<i class="fa fa-trash-o"></i> 
	                                    	</span> 
	                                    </div>
	                                <div class="clear"></div>
	                                </div>
	                            <?php
	                                foreach ($layout_value['row'] as $col_key => $layout_col){ ?>

	                                  <div class="themehunk-megamenu-col themehunk-megamenu-col-<?php echo $layout_col['col']; ?> " data-col-id="<?php echo $col_key; ?>">

	                                    <div class="themehunk-megamenu-item-wrap">
	                                        <div class="themehunk-megamenu-column-actions">
	                                        	<span class="themehunk-megamenuColSortingIcon"><i class="fa fa-arrows"></i> <?php _e('Column', 'themehunk-megamenu') ?> 
	                                    		</span>
	                                    		<span class="mmthColDeleteIcon">
	                                    			<i class="fa fa-trash-o"></i> 
	                                    		</span> 
	                                      	</div>
	                             <?php  
	                                    	foreach ( $layout_col['items'] as $key => $value ){
		                                        if ( $value['item_type'] == 'widget' && $value['widget_id'] ){
		                                            
		                                            ThemeHunk_MegaMenu_Widgets::themehunk_megamenu_widget_items($value['widget_id'], $key);
		                                            
		                                        }elseif ( $value['ID'] ){
		                                            ThemeHunk_MegaMenu_Widgets::themehunk_megamenu_menu_items( $value, $key ); 
		                                        }
		                                    }					
	                               ?>     
	                                    </div>

	                                    </div>
	                              <?php } ?> 
	                                </div>
	                          <?php } ?> 
	                         <?php } ?>
	                    </div> <!-- #themehunk_megamenu_item_layout_wrap -->
						
						<div class="megamenu-new-row-add">
				 			<div class="themehunk-megamenu-add-row-btn">
				 				<span class="dashicons dashicons-plus"></span>
				 				<span class="new-row-btn-text">New Row</span>
				 			</div>
				 		</div>	
				 	</div><!--	item-widgets-wrap mmth-limit-height -->
	            </div> <!-- .themehunk-megamenuDraggableWidgetArea -->  

		 		<div class="themehunk-megamenu-builder-config-options-content">
		 			<form method="post" class="themehunk-megamenu-builder-config-options-data">
						<table>
						  <tr>
						    <td class="mmth-name">
						    	<?php _e('Panel Width (px, %, em)', 'themehunk-megamenu') ?>
						    </td>
						    <td class="mmth-sett-optn">
						    	<input type="hidden" name="menu_item_id" value="<?php echo $menu_item_id; ?>">
						    	
						    	<input type="text" name="themehunk_megamenu_width" value="<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_width'] ) ) ? $mmth_builder_option['themehunk_megamenu_width'] : '100%'; ?>">
						    </td>
						    
						  </tr>
						  <tr>
						  <!-- chk box -->
						  	<td class="mmth-name">
                               <?php _e('Panel fit to End-to-End', 'themehunk-megamenu') ?>
                           </td>
						  <td class="mmth-sett-optn">
						            
						    	<input type="checkbox" id="themehunk_megamenu_endtoend" name="themehunk_megamenu_endtoend" value="end-to-end" <?php if($mmth_builder_option['themehunk_megamenu_endtoend']=="end-to-end") echo "checked"; ?>>
						    </td>
						</tr>
						  <!-- radio image -->
						  <tr>
						  	<td class="mmth-name">
						  		<?php _e('Panel Alingment', 'themehunk-megamenu');  ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="themehunk-megamenu-pannel-alignment">
									<div class="mmth-radio-selector">
									    <input id="panleft" type="radio" name="themehunk-megamenu-pannel-alignment" value="panleft" />
									    
									    <label class="radio-cc <?php if($mmth_builder_option['mmth_pannel_alignment']=="panleft") echo "active"; ?>" style="background-image: url(<?php echo THEMEHUNK_MEGAMENU_PANEL_LEFT ?>);" for="panleft"></label>

									    <input id="panright" type="radio" name="themehunk-megamenu-pannel-alignment" value="panright" />
									    
									    <label class="radio-cc <?php if($mmth_builder_option['mmth_pannel_alignment']=="panright") echo "active"; ?>" style="background-image: url(<?php echo THEMEHUNK_MEGAMENU_PANEL_RIGHT ?>);" for="panright"></label>
									 </div>
						  	   </label>
						  	</td>
						  </tr>
						
						  <tr>
						  	<td class="mmth-name">
						  		<?php _e('Background Image', 'themehunk-megamenu') ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		
							  	<input type="text" id="item-megamenu-bgimage-url" name="themehunk_megamenu_bg_image" value="<?php echo $mmth_builder_option['themehunk_megamenu_bg_image']; ?>">
							  	<input type="button" id="set-item-megamenu-bgimage" value="<?php _e('Upload Image', 'themehunk-megamenu') ?>">
						  		<?php $hidden = empty( $mmth_builder_option['themehunk_megamenu_bg_image'] ) ? 'hidden' : ''  ?>
						  		<p class="hide-if-no-js <?php echo $hidden; ?>">
						  			<span class="img-ovrlay">
									    <img id="item-megamenu-bgimage-container" src="<?php echo $mmth_builder_option['themehunk_megamenu_bg_image']; ?>" />
									</span><br>
										<a href="javascript:;" id="remove_themehunk_megamenu_bg_image">
											<?php _e('Remove Image', 'themehunk-megamenu') ?>
										</a>
								</p>					 
						  	</td>
						  </tr>
						    <tr>
						  	<td class="mmth-name">
						  		<?php _e('Background Color/Overlay Color', 'themehunk-megamenu') ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="mmth-mega-bg-color">
						  			<span class="mega-short-desc"><?php  _e('Color', 'themehunk-megamenu');?></span>
						  		<input type='text' class='color_picker_megamenu' name='themehunk_megamenu_bg_color' value='<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_bg_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_bg_color'] : '#fff'; ?>' style='background:<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_bg_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_bg_color'] : '#fff'; ?>' />

						  		
						  	   </label>
						  	</td>
						  </tr>
						  <tr>
						  	<td class="mmth-name">
						  		<?php _e('Panel Padding (px)', 'themehunk-megamenu'); ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="mmth-mega-pannel-top-padding">
						  			<span class="mega-short-desc"><?php  _e('Top', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_pannel_padding_top mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_padding_top" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_padding_top']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-right-padding">
						  			<span class="mega-short-desc"><?php  _e('Right', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_pannel_padding_right mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_padding_right" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_padding_right']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-bottom-padding">
						  			<span class="mega-short-desc"><?php  _e('Bottom', 'themehunk-megamenu');?></span>
						  				<input class="themehunk_megamenu_mega_pannel_padding_bottom mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_padding_bottom" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_padding_bottom']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-left-padding">
						  			<span class="mega-short-desc"><?php  _e('Left', 'themehunk-megamenu');?> </span>
						  			<input class="themehunk_megamenu_mega_pannel_padding_left mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_padding_left" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_padding_left']; ?>">
						  		</label>
						  	</td>
						  </tr>
						  <tr>
						  	<td class="mmth-name">
						  		<?php _e('Border (px)', 'themehunk-megamenu'); ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="mmth-mega-border-color">
						  			<span class="mega-short-desc"><?php  _e('Color', 'themehunk-megamenu');?></span>
						  			<input type='text' class='color_picker_megamenu' name='themehunk_megamenu_border_color' value='<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_border_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_border_color'] : '#fff'; ?>' style='background:<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_border_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_border_color'] : '#fff'; ?>' />
						  		</label>
						 
						  		<label class="mmth-mega-pannel-top-border">
						  			<span class="mega-short-desc"><?php  _e('Top', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_pannel_border_top mmth-border" type="number" name="themehunk_megamenu_mega_pannel_border_top" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_border_top']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-right-border">
						  			<span class="mega-short-desc"><?php  _e('Right', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_pannel_border_right mmth-border" type="number" name="themehunk_megamenu_mega_pannel_border_right" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_border_right']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-bottom-border">
						  			<span class="mega-short-desc"><?php  _e('Bottom', 'themehunk-megamenu');?></span>
						  				<input class="themehunk_megamenu_mega_pannel_border_bottom mmth-border" type="number" name="themehunk_megamenu_mega_pannel_border_bottom" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_border_bottom']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-left-border">
						  			<span class="mega-short-desc"><?php  _e('Left', 'themehunk-megamenu');?> </span>
						  			<input class="themehunk_megamenu_mega_pannel_border_left mmth-border" type="number" name="themehunk_megamenu_mega_pannel_border_left" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_border_left']; ?>">
						  		</label>
						  	</td>
						  </tr>
						  <tr>
						  	<td class="mmth-name">
						  		<?php _e('Border Radius (px)', 'themehunk-megamenu'); ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="mmth-mega-pannel-top-left-radius">
						  			<span class="mega-short-desc"><?php  _e('Top', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_pannel_raidus_top_left mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_raidus_top_left" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_raidus_top_left']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-top-right-radius">
						  			<span class="mega-short-desc"><?php  _e('Right', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_pannel_raidus_top_right mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_raidus_top_right" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_raidus_top_right']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-bottom-right-radius">
						  			<span class="mega-short-desc"><?php  _e('Bottom', 'themehunk-megamenu');?></span>
						  				<input class="themehunk_megamenu_mega_pannel_raidus_bottom_right mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_raidus_bottom_right" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_raidus_bottom_right']; ?>">
						  		</label>
						  		<label class="mmth-mega-pannel-bottom-left-radius">
						  			<span class="mega-short-desc"><?php  _e('Left', 'themehunk-megamenu');?> </span>
						  			<input class="themehunk_megamenu_mega_pannel_raidus_bottom_left mmth-padding" type="number" name="themehunk_megamenu_mega_pannel_raidus_bottom_left" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_pannel_raidus_bottom_left']; ?>">
						  		</label>
						  	</td>
						  </tr>
						  <tr>
						  	<td class="mmth-name">
						  		<?php _e('Column Padding (px)', 'themehunk-megamenu'); ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="mmth-mega-column-top-padding">
						  			<span class="mega-short-desc"><?php  _e('Top', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_column_padding_top mmth-padding" type="number" name="themehunk_megamenu_mega_column_padding_top" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_column_padding_top']; ?>">
						  		</label>
						  		<label class="mmth-mega-column-right-padding">
						  			<span class="mega-short-desc"><?php  _e('Right (px)', 'themehunk-megamenu');?></span>
						  			<input class="themehunk_megamenu_mega_column_padding_right mmth-padding" type="number" name="themehunk_megamenu_mega_column_padding_right" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_column_padding_right']; ?>">
						  		</label>
						  		<label class="mmth-mega-column-bottom-padding">
						  			<span class="mega-short-desc"><?php  _e('Bottom', 'themehunk-megamenu');?></span>
						  				<input class="themehunk_megamenu_mega_column_padding_bottom mmth-padding" type="number" name="themehunk_megamenu_mega_column_padding_bottom" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_column_padding_bottom']; ?>">
						  		</label>
						  		<label class="mmth-mega-column-left-padding">
						  			<span class="mega-short-desc"><?php  _e('Left', 'themehunk-megamenu');?> </span>
						  			<input class="themehunk_megamenu_mega_column_padding_left mmth-padding" type="number" name="themehunk_megamenu_mega_column_padding_left" value="<?php echo $mmth_builder_option['themehunk_megamenu_mega_column_padding_left']; ?>">
						  		</label>
						  	</td>
						  </tr>
					
						  <tr>
						  	<td class="mmth-name">
						  		<?php _e('Widget', 'themehunk-megamenu') ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="mmth-mega-widget-title-color">
						  			<span class="mega-short-desc"><?php  _e('Title color', 'themehunk-megamenu');?></span>
						  		<input type='text' class='color_picker_megamenu' name='themehunk_megamenu_themehunk_megamenu_widget_title_color' value='<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_themehunk_megamenu_widget_title_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_border_color'] : '#000'; ?>' style='background:<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_themehunk_megamenu_widget_title_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_border_color'] : '#000'; ?>' />
						  	   </label>
						  	   <label class="mmth-mega-widget-text-color">
						  			<span class="mega-short-desc"><?php  _e('Text', 'themehunk-megamenu');?></span>
						  		<input type='text' class='color_picker_megamenu' name='themehunk_megamenu_megamenu_widget_text_color' value='<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_megamenu_widget_text_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_megamenu_widget_text_color'] : '#000'; ?>' style='background:<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_megamenu_widget_text_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_megamenu_widget_text_color'] : '#000'; ?>' />
						  	   </label>
						  	   <label class="mmth-mega-widget-link-color">
						  			<span class="mega-short-desc"><?php  _e('Link', 'themehunk-megamenu');?></span>
						  		<input type='text' class='color_picker_megamenu' name='themehunk_megamenu_megamenu_widget_link_color' value='<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_megamenu_widget_link_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_megamenu_widget_link_color'] : '#000'; ?>' style='background:<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_megamenu_widget_link_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_megamenu_widget_link_color'] : '#000'; ?>' />
						  	   </label>
						  	   <label class="mmth-mega-widget-linkhvr-color">
						  			<span class="mega-short-desc"><?php  _e('Link Hover', 'themehunk-megamenu');?></span>
						  		<input type='text' class='color_picker_megamenu' name='themehunk_megamenu_megamenu_widget_linkhvr_color' value='<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_megamenu_widget_linkhvr_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_megamenu_widget_linkhvr_color'] : '#000'; ?>' style='background:<?php echo isset( ( $mmth_builder_option['themehunk_megamenu_megamenu_widget_linkhvr_color'] ) ) ? $mmth_builder_option['themehunk_megamenu_megamenu_widget_linkhvr_color'] : '#000'; ?>' />
						  	   </label>
						  	</td>	
						  </tr>
						  
                          <tr>
						  	<td class="mmth-name">
						  		<?php _e('Widget Content Alingment', 'themehunk-megamenu'); ?>
						  	</td>
						  	<td class="mmth-sett-optn">
						  		<label class="themehunk-megamenu-pannel-alignment">
									<div class="mmth-radio-selector">
										
									    <input id="left" type="radio" name="mmth-widget-content-alignment" value="left" />
									    <label class="radio-cc <?php if($mmth_builder_option['themehunk_megamenu_widget_content_alignment']=="left") echo "active"; ?>" style="background-image: url(<?php echo THEMEHUNK_MEGAMENU_ALIGN_LEFT ?>);" for="left"></label>

                                        <input id="center" type="radio" name="mmth-widget-content-alignment" value="center" />
									    <label class="radio-cc <?php if($mmth_builder_option['themehunk_megamenu_widget_content_alignment']=="center") echo "active"; ?>" style="background-image: url(<?php echo THEMEHUNK_MEGAMENU_ALIGN_CENTER ?>);" for="center"></label>

									    <input id="right" type="radio" name="mmth-widget-content-alignment" value="right" /> 
									    <label class="radio-cc <?php if($mmth_builder_option['themehunk_megamenu_widget_content_alignment']=="right") echo "active"; ?>" style="background-image: url(<?php echo THEMEHUNK_MEGAMENU_ALIGN_RIGHT ?>);" for="right"></label>

									    
									 </div>
						  	   </label>
						  	</td>
						  </tr>


						  	  <tr>
						  	<td class="mmth-name"><?php 
	                        	submit_button( 
	                        					__( 'Save' ), 
	                        					'button-primary alignleft mmth-builder-options-submit', 
	                        					'themehunk_megamenu_builder_options', 
	                        					false 
	                        				);
                            ?></td>
						  	<td class="mmth-sett-optn">
	                        
						  	</td>
						  </tr>
						</table>
					</form>
		 		</div>
	            <div class="themehunk-megamenu-icons-container">
	                <div class="themehunk-megamenu-icons-menu">
	                    <div class="themehunk-megamenu-icons-topbar-left">
	                        <ul>
	                            <li>
	                            	<a href="#icons-tabs-1" class='icon-tabs-nav active' data-icon-tabs='icons-tabs-1'>
	                            		<?php _e('Dashicons', 'themehunk-megamenu'); ?>
	                            	</a>
	                            </li>
	                            <li>
	                            	<a href="#icons-tabs-2" class='icon-tabs-nav' data-icon-tabs='icons-tabs-2'>
	                            		<?php _e('Font Awesome', 'themehunk-megamenu'); ?>    		
	                            	</a>
	                            </li>
	                            <li>
	                            	<a href="#icons-tabs-3" class='icon-tabs-nav' data-icon-tabs='icons-tabs-3'>
	                            		<?php _e('IcoFont', 'themehunk-megamenu'); ?>		
	                            	</a>
	                            </li>
	                        </ul>
	                    </div>

	                    <div class="themehunk-megamenu-icons-topbar-right">
	                        <div class="mmth-icon-search-wrap">
	                            <input id="themehunk_megamenu_icons_search" type="text" value="" placeholder="<?php _e('Search...', 'themehunk-megamenu'); ?>">
	                            <i class="fa fa-search"></i>
	                        </div>
	                    </div>

	                    <div class="clear"></div>
	                </div>

	                <div class="themehunk-megamenu-icons-tab-content mmth-limit-height">

	                    <div id="icons-tabs-1">
	                        <?php
	                        $dashicons = themehunk_megamenu_dashicons();

	                        $current_icon = '';
	                        if ( ! empty($mmth_builder_option['icon'])){
	                            $current_icon = $mmth_builder_option['icon'];
	                        }
	                        echo "<a href='javascript:;' class='themehunk-megamenu-icons' data-icon='' title=''>&nbsp;</a>";
	                        foreach ($dashicons as $di_key => $di_name){
	                            $selected_icon = ($current_icon == 'dashicons '.$di_key) ? 'themehunk-megamenu-icon-selected' :'';
	                            echo "<a href='javascript:;' class='themehunk-megamenu-icons {$selected_icon} ' data-icon='dashicons {$di_key}' title='{$di_name}'>
	                            <i class='dashicons {$di_key}'></i></a>";
	                        }
	                        ?>
	                    </div>

	                    <div id="icons-tabs-2">
	                        <?php
	                        // $font_awesome = mmth_font_awesome();
	                        echo "<span class='themehunk-megamenu-pro'><i class='dashicons dashicons-lock'></i> This is available in Pro version</span>";
	                        
	                        ?>
	                    </div>	

	                    <div id="icons-tabs-3">
                            <?php
                            // $icofonts = mmth_icofont();
                             echo "<span class='themehunk-megamenu-pro'><i class='dashicons dashicons-lock'></i> This is available in Pro version</span>";
                            ?>
                        </div>	        
	                </div>
	            </div>

	 		</div><!-- .mmth-builder  -->

	 		<?php }else {?>
	 			<div class="mmth-no-mega-menu">
	 				<?php _e('Mega Menu will only work on top level menu items.', 'themehunk-megamenu') ?>
	 			</div>
		 		<?php } ?>
 		</div>	
 	</div>
 </div>