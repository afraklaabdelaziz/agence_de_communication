<?php
if (!defined('ABSPATH')) exit;
class wp_popup_builder_init
{

	function initColumn($column)
	{
		$popupColumn = '';
		foreach ($column as $value) {
			$uniqIdAttr = isset($value["id"]) ? 'data-uniqid="' . $value["id"] . '"' : '';
			$style = isset($value["style"]) ? 'style="' . $value["style"] . '"' : '';
			$popupValueContent = isset($value['content']) && is_array($value['content']) && !empty($value['content']) ? $this->initContent($value['content']) : '';
			$popupColumn .= '<div ' . $uniqIdAttr . ' data-rl-column="1" class="wppb-popup-rl-column rlEditorDropable" ' . $style . '>' . $popupValueContent . '</div>';
		}
		return $popupColumn;
	}

	function initContent($column_content)
	{
		$popupContent = '';
		foreach ($column_content as $setting_value) {
			$Style = isset($setting_value['style']) ? 'style="' . $setting_value['style'] . '"' : '';
			$alignMent = isset($setting_value['alignment']) ? 'style="justify-content:' . $setting_value['alignment'] . ';"' : '';
			$alignMentContent = $alignMent ? 'data-content-alignment="' . $setting_value['alignment'] . '"' : '';
			$dataLink = isset($setting_value['link']) ? "data-editor-link='" . $setting_value['link'] . "'" : '';
			$dataLinktarget = isset($setting_value['target']) ? "data-editor-link-target='" . $setting_value['target'] . "'" : '';
			$uniqIdAttr = isset($setting_value["id"]) ? 'data-uniqid="' . $setting_value["id"] . '"' : '';
			$contentAttr = $Style . $alignMentContent . $dataLink . $dataLinktarget . $uniqIdAttr;
			if ($setting_value['type'] == 'text') {
				$popupContent .=	'<div class="data-rl-editable-wrap" ' . $alignMent . '>
									<div class="actions_">
									<span class="dashicons dashicons-no rlRemoveElement"></span></div>
									<span data-rl-editable="text" ' . $contentAttr . '>' . $setting_value['content'] . '</span>
								</div>';
			} elseif ($setting_value['type'] == 'heading') {
				$popupContent .=	'<div class="data-rl-editable-wrap" ' . $alignMent . '>
									<div class="actions_">
									<span class="dashicons dashicons-no rlRemoveElement"></span></div>
									<span class="text-heading" data-rl-editable="heading" ' . $contentAttr . '>' . $setting_value['content'] . '</span>
								</div>';
			} elseif ($setting_value['type'] == 'spacer') {
				$popupContent .=	'<div class="data-rl-editable-wrap">
											<div class="actions_">
											<span class="dashicons dashicons-no rlRemoveElement"></span>
										</div>
										<span data-rl-editable="spacer" ' . $contentAttr . '></span>
									</div>';
			} elseif ($setting_value['type'] == 'image') {

				$popupContent .= '<div class="data-rl-editable-wrap wrap-image_" ' . $alignMent . '>
												<div class="actions_">
												<span class="dashicons dashicons-no rlRemoveElement"></span>
											 </div>
											 <img src="' . $setting_value['image-url'] . '"  ' . $contentAttr . ' data-rl-editable="image">
											</div>';
			} elseif ($setting_value['type'] == 'link') {
				$popupContent .=	'<div class="data-rl-editable-wrap" ' . $alignMent . '>
									<div class="actions_">
									<span class="dashicons dashicons-no rlRemoveElement"></span></div>
									<span data-rl-editable="link" ' . $contentAttr . '>' . $setting_value['content'] . '</span>
								</div>';
			} elseif ($setting_value['type'] == 'lead-form' && (isset($setting_value['content']) && is_numeric($setting_value['content'])) && wppb_db::lead_form_front_end()) {
				$leadForm_Form = wppb_db::lead_form_front_end()->lfb_show_front_end_forms($setting_value['content']);
				$formStyles = '';
				if (isset($setting_value['styles'])) {
					$formStyles = htmlspecialchars(json_encode($setting_value['styles']), ENT_COMPAT);
					$formStyles = 'data-form-styles="' . $formStyles . '"';
				}

				$submitAlign = '';
				if (isset($setting_value['styles']['submit-align'])) {
					$submitAlign = 'lf_submit_' . $setting_value['styles']['submit-align'];
				}
				$popupContent .= '<div class="data-rl-editable-wrap" ' . $alignMent . '>
								<div class="actions_"><span class="dashicons dashicons-no rlRemoveElement"></span></div>
								<div class="wppb-popup-lead-form ' . $submitAlign . '" ' . $uniqIdAttr . ' data-form-id="' . $setting_value['content'] . '" ' . $formStyles . '>
									' . $leadForm_Form . '
									</div>
									</div>';
			} else if ($setting_value['type'] == 'shortcode' && isset($setting_value['content'])) {
				$shortCode = $setting_value['content'];
				$style_ = isset($setting_value['wrap-style']) ? $setting_value['wrap-style'] : '';
				$shortCode_ = '';
				$shortCode_ .= '<div class="wppb-popup-shortcode" data-shortcode="' . $shortCode . '" style="' . $style_ . '" ' . $uniqIdAttr . '>';
				$shortCode_ .= do_shortcode($shortCode);
				$shortCode_ .= "</div>";
				$popupContent .= '<div class="data-rl-editable-wrap" ' . $alignMent . '>
					<div class="actions_">
					<span class="dashicons dashicons-admin-page rlCopyElement"></span>
					<span class="dashicons dashicons-no rlRemoveElement"></span>
					</div>
					' . $shortCode_ . '
				</div>';
			}
		}
		return $popupContent;
	}

	function popup_layout($popupSetData, $layout = '')
	{
		$overlay_image = $popupSetData['overlay-image-url'] ? 'background-image:url(' . $popupSetData['overlay-image-url'] . ');' : '';
		$overlayStyle = $overlay_image ? $overlay_image . $popupSetData['overlay-style'] : '';

		$globalHeight = $popupSetData["wrapper-height"] != 'auto' ? $popupSetData["wrapper-height"] . 'px;' : $popupSetData["wrapper-height"] . ';';
		$globalStyle = "padding:" . $popupSetData["global-padding"] . ";height:" . $globalHeight;

		$return = $popupSetData["close-btn"] . '<div class="wppb-popup-custom-wrapper" style="' . $popupSetData["wrapper-style"] . '">
			         <div class="wppb-popup-overlay-custom-img" data-overlay-image="' . $popupSetData['overlay-image-url'] . '" style="' . $overlayStyle . '"></div>
			          <div class="wppb-popup-custom-overlay" style="background-color:' . $popupSetData['overlay-color'] . ';"></div>
			              <div class="wppb-popup-custom-content" style="' . $globalStyle . '">
				            ' . $popupSetData["content"] . '
			              </div>
			        </div>';
		return $return;
	}

	// popup page list of all popupSetData content
	public function wppbPopupContent($allSetting)
	{
		$popupSetData = array(
			'wrapper-style'		=> '',
			'wrapper-height'	=> 'auto',
			'overlay-image-url' => '',
			'overlay-style'		=> "",
			'overlay-color'		=> '#28292C91',
			'outside-color'		=> '#cdcbcb',
			'content' 			=> '',
			'global-padding'	=> '23px 37px',
			'layout' 			=> '',
			'close-btn' 		=> '',
			'popup-name' 		=> __('New Popup name', 'wppb')
		);
		foreach ($allSetting as $setting_value) {
			if (isset($setting_value['content']) && is_array($setting_value['content'])) {
				if ($setting_value['type'] == 'global-setting') {
					foreach ($setting_value['content'] as $contentkey_ => $contentvalue_) {
						if (isset($popupSetData[$contentkey_])) $popupSetData[$contentkey_] = $contentvalue_;
					}
				} elseif ($setting_value['type'] == 'wrap') {
					$popupContentColumn = $this->initColumn($setting_value['content']);
					$popupSetData['content'] =	'<div data-rl-wrap="" class="wppb-popup-rl-wrap rl-clear">' . $popupContentColumn . '</div>';
				}
			} else if ($setting_value['type'] == "close-btn") {
				$uniqIdAttr = isset($setting_value["id"]) ? 'data-uniqid="' . $setting_value["id"] . '"' : '';
				$styleClose = isset($setting_value['style']) ? "style='" . $setting_value['style'] . "'" : '';
				$popupSetData["close-btn"] = '<span ' . $uniqIdAttr . ' class="wppb-popup-close-btn dashicons dashicons-no-alt" ' . $styleClose . '></span>';
			}
		}
		return $popupSetData;
	}
	// popup page list of all popupSetData
	public function wppbPopupList($allSetting, $business_id, $isActive = false, $device_)
	{
		$popup_is_active = $isActive ? "checked='checked'" : "";
		$popup_name = isset($allSetting[0]['content']['popup-name']) && $allSetting[0]['content']['popup-name'] ? $allSetting[0]['content']['popup-name'] : '';
		$business_id = $business_id ? $business_id : "";
		$url         = WPPB_PAGE_URL . '&custom-popup=' . $business_id;

		$editBtn     = '<a class="wppb-popup-setting can_disable" href="' . esc_url($url) . '"><span class="dashicons dashicons-edit"></span></a>';
		$deLeteBtn     = '<a data-bid="' . $business_id . '" class="wppb_popup_deleteAddon dashicons dashicons-trash"></a>';

		$status = '<div class="wppb-popup-checkbox">
				<input id="business_popup--' . $business_id . '" type="checkbox" class="wppb_popup_setting_active" data-bid="' . $business_id . '" ' . $popup_is_active . '>
				<label for="business_popup--' . $business_id . '"></label>
			</div>';
		$all 	 = !$device_ || $device_ == "all" ? 'checked' : '';
		$mobileEnable = '<div>
					        <input data-device="' . $business_id . '" id="wppb-device-name-all' . $business_id . '" type="radio" name="device-' . $business_id . '" value="all" ' . $all . '>
					        <label for="wppb-device-name-all' . $business_id . '"><span class="dashicons dashicons-admin-site-alt3"></span></label>
					    </div>';
		$homePage = get_home_url() . "?wppb_preview=" . $business_id;
		$homePage = '<a href="' . $homePage . '" target="_blank"><span class="dashicons dashicons-visibility"></span></a>';
		$Setting_ = '<a href="' . $url . '&wppb-setting"><span class="dashicons dashicons-admin-generic"></span></a>';

		$returnHtml = '';
		$returnHtml .= "<div class='wppb-list-item'>";
		$returnHtml .= '<div class="wppb-popup-list-title"><span>' . $popup_name . '</span></div>';
		$returnHtml .= '<div class="wppb-popup-list-enable"><span>' . $status . '</span></div>';
		$returnHtml .= '<div class="wppb-popup-list-mobile">' . $mobileEnable . '</div>';
		$returnHtml .= '<div class="wppb-popup-list-view">' . $homePage . '</div>';
		$returnHtml .= '<div class="wppb-popup-list-action"><span>' . $deLeteBtn . $editBtn . '</span></div>';
		$returnHtml .= '<div class="wppb-popup-list-setting">' . $Setting_ . '</div>';
		$returnHtml .= "</div>";
		return $returnHtml;
	}
	// popup page list of all popupSetData json file
	public function wppbPopupList_json($allSetting, $column_making, $countPopup)
	{
		$imageUrl = isset($allSetting[0]['img-url']) ? $allSetting[0]['img-url'] : '';
		$imageUrl = $imageUrl ? "<img src='" . $imageUrl . "'>" : '';
		$prebuilt_id = 'wppb-prebuilt-id-' . $column_making;
		$popupSetData = $this->wppbPopupContent($allSetting);
		$attr_inbuilt = isset($popupSetData['layout']) && $popupSetData['layout'] ? 'data-layout="' . $popupSetData['layout'] . '"' : '';
		$attr_inbuilt .= isset($popupSetData['outside-color']) && $popupSetData['outside-color'] ? 'data-outside-color="' . $popupSetData['outside-color'] . '"' : '';
		$attr_inbuilt .= "data-prebuilt-id='" . $prebuilt_id . "'";
		$popupSetData = $this->popup_layout($popupSetData);
		$popupSetData = "<div data-layout='" . $prebuilt_id . "'>" . $popupSetData . '</div>';
		$returnHtml = ['prebuilt-html' => $popupSetData, 'prebuilt-label' => ''];
		if ($column_making == 1) $returnHtml['prebuilt-label'] .= '<div class="wppb-popup-row wppb-popup_clear">';
		$returnHtml['prebuilt-label'] .= '<div class="wppb-popup-column-three">
								<input id="wppb-popup-layout-label__layout--' . $column_making . '" type="radio" name="wppb-popup-layout" value="prebuilt" ' . $attr_inbuilt . '>
								<label for="wppb-popup-layout-label__layout--' . $column_making . '" class="wppb-popup-json-label">' . $imageUrl . '</label>
						</div>';
		if ($countPopup == ($column_making)) {
			$returnHtml['prebuilt-label'] .= '</div>';
		} elseif (($column_making) % 3 === 0) {
			$returnHtml['prebuilt-label'] .= '</div><div class="wppb-popup-row wppb-popup_clear">';
		}
		return $returnHtml;
	}

	// shortcode 

	public function show_popup_part_start($value, $shortcode = false)
	{
		$return_data = false;
		$cookieFilter = true;
		$option = unserialize($value->boption);
		if (isset($_COOKIE['wppb-fr-' . $value->BID]) && isset($option['frequency']) && $_COOKIE['wppb-fr-' . $value->BID] == $option['frequency']) {
			$cookieFilter = false;
		}
		if ($cookieFilter) {
			$device = isset($option['device']) ? $option['device'] : false;
			$checkMobile = wp_is_mobile();
			// if ( $device == 'mobile' && $checkMobile ) { //desktop condition
			if (($device == 'mobile' || isset($option['mobile-enable'])) && $checkMobile) { //desktop condition and also for previous user
				$return_data = true;
			} else if ($device == 'desktop' && !$checkMobile) { //mobile condition
				$return_data =  true;
			} else if ($device == 'all' || $device == false) { //all and if not device set
				$return_data =  true;
			}
		}
		return $return_data ? $this->show_popup_part_one($value, $option, $shortcode) : false;
	}

	public function show_popup_part_one($value, $option, $shortcode)
	{
		$return_ = false;
		$setting_ = [];
		$popup_attr = '';
		$placement = isset($option['placement']) ? $option['placement'] : false;
		// if ( $placement == 'all' ) {//new user
		if ($placement == 'all' || (isset($option['all']) && $option['all'])) {
			$return_ = true;
			// }else if ( $placement == 'home_page' && is_front_page() ) { for new update
		} else if (($placement == 'home_page' && is_front_page()) || (isset($option['home_page']) && $option['home_page'])) {
			$return_ = true;
		}

		// class and attr by trigger
		if (isset($option['trigger'])) {
			$trigger = $option['trigger'];
			//for page load 
			if (isset($trigger['page-load'])) {
				if (!$trigger['page-load'] || $trigger['page-load'] == 'false') $return_ = false;;
			}
			//for setting like popup open delay 
			if (isset($trigger['time'])) {
				$minute_ = isset($trigger['time']['minute']) && is_numeric(isset($trigger['time']['minute'])) ? $trigger['time']['minute'] * 60 : false;
				$second_ = $minute_ ? $minute_ + $trigger['time']['second'] : $trigger['time']['second'];
				$setting_['popup-delay-open'] = $second_;
			}
		}
		// for frequency 
		if (isset($option['frequency']) && $option['frequency']) {
			$popup_attr .= 'data-wppb-frequency="' . $option['frequency'] . '"';
			$popup_attr .= 'data-wppb-bid="' . $value->BID . '"';
		}

		if ($shortcode || $return_) {
			$popupHtml = new wppb_db();
			$popupHtmlContent = $popupHtml->wppb_html($value->setting, '', $setting_);
			$showPopup = $popupHtmlContent ? '<div data-option="1" class="wppb-popup-open popup active" ' . $popup_attr . '>' . $popupHtmlContent . '</div>' : '';
			if ($showPopup) return $showPopup;
		}
	}
	// shortcode 



	// builder internal tools function
	public function wppb_changeFilePath($arr, $path)
	{
		$return = [];
		if (is_array($arr)) {
			foreach ($arr as $key => $value) {
				if (is_array($value)) {
					$return[$key] = $this->wppb_changeFilePath($value, $path);
				} else {
					if ($key == 'image-url' || $key == 'overlay-image-url' || $key == 'img-url') {
						$Exp = explode('/', $value);
						$End = end($Exp);
						$return[$key] = $path . $End;
					} else {
						$return[$key] = $value;
					}
				} //else
			} //foreach
		}
		return $return;
	}

	public function header_title($title)
	{
		echo '<div class="rl_i_editor-header-title">
						<label>' . $title . '</label>
					</div>';
	}

	public function color($title, $prop, $type, $color_id = 1, $attr = '')
	{
		if ($title && $prop && $type) {
			$typeAndProp = $type . '="' . $prop . '"' . $attr;
			echo '<div class="rl_i_editor-item-content-items item-text inline__"><label class="rl-sub-title">' . $title . '</label>
					<div>
						<label class="color-output" data-input-color="' . $color_id . '" ' . $typeAndProp . '></label>
					</div>
				</div>';
		}
	}

	public function range_slider($title, $id, $arr, $id_two = false, $type_ = "data-global-input")
	{
		$title_ = isset($arr['title']) ? $arr['title'] : '';
		$attr  = isset($arr['min']) ? 'min="' . $arr['min'] . '"' : '';
		$attr .= isset($arr['max']) ? 'max="' . $arr['max'] . '"' : '';
		$attr .= isset($arr['value']) ? 'value="' . $arr['value'] . '"' : '';
		$attr .= $attrTwo = isset($arr['attr']) ? $arr['attr'] : '';
		$id_two = !$id_two ? $id : $id_two;

		$attr .=  $type_ . '="' . $id . '"';
		$container = isset($arr['container-class']) ? $arr['container-class'] : '';
		$html = '<div  class="rl_i_editor-item-content-items inline__ ' . $container . '">';
		$html .= '<label class="rl-sub-title range-titile">' . $title . '</label>';
		$html .= '<div class="range_ rl_i_range-font-size">
							<input data-show-range="' . $id_two . '" type="range" ' . $attr . '>
						</div>';
		$html .= '<div class="data-range-output">
							<input class="rl-sub-title" type="number" data-range-output="' . $id_two . '" ' . $attrTwo . '>';
		$html .= '</div>
					</div>';
		echo $html;
	}
	public function select($attr, $option)
	{
		$return = "<select class='rl-sub-title' " . $attr . ">";
		if (is_array($option)) {
			foreach ($option as $value) {
				if (isset($value[0]) && isset($value[1])) {
					$selected = isset($value[2]) ? 'selected="selected"' : '';
					$return	.= "<option value='" . $value[1] . "' " . $selected . ">" . $value[0] . "</option>";
				} elseif (isset($value[0])) {
					$return	.= "<option>" . $value[0] . "</option>";
				}
			}
		}
		$return	.= "</select>";
		return $return;
	}
	public function checkbox($id, $title, $attr)
	{
		return '<div  class="rl_i_editor-item-content-items title_ inline__">
			<div class="rl_i_range-font-size">
					<div class="wppb-popup-checkbox-container">
						<label class="wppb-popup-checkbox-title rl-sub-title">' . $title . '</label>
						<div class="wppb-popup-checkbox">
							<input id="wppb_popup__checkbox__label_id-' . $id . '" type="checkbox" ' . $attr . '>
							<label for="wppb_popup__checkbox__label_id-' . $id . '"></label>
						</div>
					</div>
				</div>
				</div>';
	}
	public function border($id, $type, $attr = '')
	{
		$data_attr = $type . '="' . $id . '"' . $attr;
		$border = $this->select($data_attr . ' data-border="border-style"', [['solid', 'solid'], ['dashed', 'dashed'], ['dotted', 'dotted'], ['double', 'double'], ['groove', 'groove'], ['ridge', 'ridge']]);
		$return =  '<section class="content-style-border">
						' . $this->checkbox($id, "Border", $data_attr . ' data-border="border-enable"') . '
					<div  class="rl_i_editor-item-content-items content-border">
						<div>
							<label class="rl-sub-title">' . __('Border Width(<small>px</small>)', 'wppb') . '</label>
							<div><input class="rl-sub-title" type="number" value="" ' . $data_attr . ' data-border="width"></div>
						</div>
						<div>
							<label class="rl-sub-title">' . __('Border radius(<small>px</small>)', 'wppb') . '</label>
							<div><input class="rl-sub-title" type="number" value="" ' . $data_attr . ' data-border="radius"></div>
						</div>
						<div>
							<label class="rl-sub-title">' . __('Border Color', 'wppb') . '</label>
							<div><label class="color-output" ' . $data_attr . ' data-input-color="border-color"></label></div>
						</div>
						<div>
							<label class="rl-sub-title">' . __('Border Style', 'wppb') . '</label>
							<div>' . $border . '</div>
						</div>
					</div>
				</section>';
		echo $return;
	}
	public function box_shadow($id, $type, $attr = '')
	{
		$data_attr = $type . '="' . $id . '"' . $attr;
		$return = '<section class="content-style-border content-style-box-shadow">
						' . $this->checkbox($type, 'Box Shadow', $data_attr . ' data-shadow="enable"') . '
							<div  class="rl_i_editor-item-content-items content-border content-box-shadow">
								<div>
									<label class="rl-sub-title">X Offset</label>
									<div><input class="rl-sub-title" type="number" value="" ' . $data_attr . '  data-shadow="x-offset"></div>
								</div>
								<div>
									<label class="rl-sub-title">Y Offset</label>
									<div><input class="rl-sub-title" type="number" value="" ' . $data_attr . ' data-shadow="y-offset"></div>
								</div>
								<div>
									<label class="rl-sub-title">Blur</label>
									<div><input class="rl-sub-title" type="number" value="" ' . $data_attr . ' data-shadow="blur"></div>
								</div>
								<div>
									<label class="rl-sub-title">Spread</label>
									<div><input class="rl-sub-title" type="number" value="" ' . $data_attr . ' data-shadow="spread"></div>
								</div>
								<div>
									<label class="rl-sub-title">Color</label>
									<div><label class="color-output" ' . $data_attr . ' data-shadow="color"></label></div>
								</div>
							</div>
						</section>';
		return $return;
	}
	public function margin_padding($id, $title, $type, $margin_padding, $attr = '')
	{
		$attr = $type . "='" . $id . "'" . $attr;
		$parameter = $margin_padding == "m" ? 'margin' : 'padding';
		$return = '<div class="rl_i_editor-item-content-items title_ inline_">
		<div class="rl_i_range-font-size"><label class="rl-sub-title">' . $title . '</label></div>
		</div>
			<div class="rl_i_editor-item-content-items inline_">
				<div class="rl_i_editor-item-content-padding_ paraMeterContainer__">
					<ul class="ul-inputs-margin-padding rl-clear">
						<li>
							<input class="rl-sub-title" type="number" value="" ' . $attr . ' data-' . $parameter . '="top">
						</li>
						<li>
							<input class="rl-sub-title" type="number" value="" ' . $attr . ' data-' . $parameter . '="right">
						</li>
						<li>
							<input class="rl-sub-title" type="number" value="" ' . $attr . ' data-' . $parameter . '="bottom">
						</li>
						<li>
							<input class="rl-sub-title" type="number" value="" ' . $attr . ' data-' . $parameter . '="left">
						</li>
						<li class="padding-origin_ margin-padding-origin">
							<input id="m__p_origin-' . $parameter . '-' . $id . '" type="checkbox" ' . $attr . ' data-origin="' . $parameter . '">
							<label for="m__p_origin-' . $parameter . '-' . $id . '"><span class="dashicons dashicons-admin-links"></span></label>
						</li>
					</ul>							
					<ul class="ul-inputs-text rl-clear">
						<li>' . __('TOP', 'wppb') . '</li>
						<li>' . __('RIGHT', 'wppb') . '</li>
						<li>' . __('BOTTOM', 'wppb') . '</li>
						<li>' . __('LEFT', 'wppb') . '</li>
						<li></li>
					</ul>
				</div>
			</div>';
		echo $return;
	}
	public function alignment($title, $id, $type, $attr = '', $number_ = false)
	{
		$attr_ = $type . "='" . $id . "'" . $attr;
		$return = '<div class="rl_i_editor-item-content-items item-alignment_ inline__">';
		// $return .= '<label class="rl-sub-title">'.$title.'</label>';
		$return .= '<div class="rl_text-alignment">
					<ul class="text-alignment-choice">
						<li>
							<input id="_alignment_label_' . $id . '_left" ' . $attr_ . ' type="radio" name="' . $id . '" value="left">
							<label for="_alignment_label_' . $id . '_left" class="dashicons dashicons-editor-alignleft"></label>
						</li>
						<li>
							<input id="_alignment_label_' . $id . '_center" ' . $attr_ . ' type="radio" name="' . $id . '" value="center">
							<label for="_alignment_label_' . $id . '_center" class="dashicons dashicons-editor-aligncenter"></label>
						</li>';
		if ($number_ != 2) {
			$return .= '<li>
							<input id="_alignment_label_' . $id . '_right" ' . $attr_ . ' type="radio" name="' . $id . '" value="right">
							<label for="_alignment_label_' . $id . '_right" class="dashicons dashicons-editor-alignright"></label>
						</li>';
		}

		$return .=	'</ul>
				</div>
			</div>';
		return $return;
	}
	// class end
}

// $wp_builder_obj = new wp_popup_builder_init();
