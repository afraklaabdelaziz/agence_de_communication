import $ from 'jquery';

$(document.body).on('wc_stripe_get_billing_prefix', (e, prefix) => {
    if (!$('[name="billing_same_as_shipping"]').is(':checked')) {
        prefix = 'shipping';
    } else {
        prefix = 'billing';
    }
    return prefix;
});