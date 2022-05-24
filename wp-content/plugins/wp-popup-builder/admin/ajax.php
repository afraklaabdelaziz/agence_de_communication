<?php
if (!defined('ABSPATH')) exit;
class wppb_ajax extends wppb_db
{
	public static $instance;
	function __construct()
	{
		parent::__construct();
		add_action('wp_ajax_custom_insert', array($this, 'insert'));
		add_action('wp_ajax_custom_update', array($this, 'update'));
		add_action('wp_ajax_delete_popup', array($this, 'delete'));
		add_action('wp_ajax_popup_active', array($this, 'update'));

		add_action('wp_ajax_option_update', array($this, 'option_update'));

		add_action('wp_ajax_getLeadForm', array($this, 'getLeadForm'));

		add_action('wp_ajax_activate_lead_form', array($this, 'lead_form_plugin_activate'));
		// shortcode api save 
		add_action('wp_ajax_shortcode_Api_Add', array($this, 'shortcode_Api_Add'));
		add_action('wp_ajax_nopriv_shortcode_Api_Add', array($this, 'shortcode_Api_Add'));
	}
	public static function get()
	{
		return self::$instance ? self::$instance : self::$instance = new self();
	}

	public function insert()
	{
		$result = $this->popup_insert();
		echo $result ? $result : 0;
		die();
	}
	public function update()
	{
		$result = $this->popup_update();
		echo $result ? $result : 0;
		die();
	}
	public function delete()
	{
		$result = $this->popup_delete();
		echo $result ? $result : 0;
		die();
	}
	public function option_update()
	{
		$result = $this->opt_update();
		echo $result ? $result : 0;
		die();
	}

	// lead form

	// install lead form 
	public function lead_form_plugin_activate()
	{
		$plugin_init = '/lead-form-builder/lead-form-builder.php';
		$activate = activate_plugin($plugin_init, '', false, true);
		if (is_wp_error($activate)) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => $activate->get_error_message(),
				)
			);
		}
		wp_send_json_success(
			array(
				'success' => self::lead_form_opt(),
				'message' => __('Plugin Successfully Activated', 'wppb'),
			)
		);
		die();
	}


	public function getLeadForm()
	{
		$result = $this->get_lead_form_ajx();
		echo $result ? $result : 0;
		die();
	}
	//shortcode 
	public function shortcode_Api_Add()
	{
		$dataPost = $_POST;
		if (isset($dataPost['shortcode'])) {
			$shortcode_ = sanitize_text_field($dataPost['shortcode']);
			$result = do_shortcode($shortcode_);
			echo $result;
		}
		die();
	}
}
wppb_ajax::get();
