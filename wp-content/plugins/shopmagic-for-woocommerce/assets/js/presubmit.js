jQuery(document).ready(function ($) {
	if (typeof shopmagic_presubmit_params === 'undefined') {
		return false;
	}

	var params = shopmagic_presubmit_params;

	var email = '';
	var $checkout_form = $('form.checkout');
	var email_fields = params.email_capture_selectors;
	var checkout_fields = params.checkout_capture_selectors;
	var checkout_fields_data = {};
	var language = params.language;
	var capture_email_xhr;

	$.each(checkout_fields, function (i, field_name) {
		checkout_fields_data[field_name] = '';
	});

	function captureEmail() {
		if (!$(this).val() || email === $(this).val()) {
			return;
		}

		email = $(this).val();

		var data = {
			email: email,
			language: language,
			checkout_fields: getCheckoutFieldValues()
		};

		if (capture_email_xhr) {
			capture_email_xhr.abort();
		}

		capture_email_xhr = $.post(params.capture_email_url, data, function (response) {
		});
	}

	function captureCheckoutField() {
		var field_name = $(this).attr('name');
		var field_value = $(this).val();

		if (!field_name || checkout_fields.indexOf(field_name) === -1) {
			return;
		}

		// Don't capture if the field is empty or hasn't changed
		if (!field_value || checkout_fields_data[field_name] === field_value) {
			return;
		}

		checkout_fields_data[field_name] = field_value;

		$.post(params.capture_checkout_field_url, {
			field_name: field_name,
			field_value: field_value
		});
	}

	/**
	 * Get the current values for checkout fields.
	 *
	 * @return object
	 */
	function getCheckoutFieldValues() {
		var fields = {};

		$.each(checkout_fields, function (i, field_name) {
			fields[field_name] = $('form.woocommerce-checkout [name="' + field_name + '"]').val();
		});

		return fields;
	}


	$(document).on('blur change', email_fields.join(', '), captureEmail);
	$checkout_form.on('change', 'select', captureCheckoutField);
	$checkout_form.on('blur change', '.input-text', captureCheckoutField);
});
