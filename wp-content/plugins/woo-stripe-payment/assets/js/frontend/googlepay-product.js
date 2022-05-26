(function ($, wc_stripe) {

    /**
     * @constructor
     */
    function GPay() {
        wc_stripe.BaseGateway.call(this, wc_stripe_googlepay_product_params);
        window.addEventListener('hashchange', this.hashchange.bind(this));
    }

    /**
     * [prototype description]
     * @type {[type]}
     */
    GPay.prototype = $.extend({}, wc_stripe.BaseGateway.prototype, wc_stripe.ProductGateway.prototype, wc_stripe.GooglePay.prototype);

    /**
     * @return {[type]}
     */
    GPay.prototype.initialize = function () {
        if (!$(this.container).length) {
            return setTimeout(this.initialize.bind(this), 1000);
        }
        wc_stripe.ProductGateway.call(this);
        this.createPaymentsClient();
        this.isReadyToPay().then(function () {
            $(this.container).show();
            $(this.container).parent().parent().addClass('active');
        }.bind(this))
    }

    /**
     * @return {[type]}
     */
    GPay.prototype.create_button = function () {
        wc_stripe.GooglePay.prototype.create_button.apply(this, arguments);
        $('#wc-stripe-googlepay-container').append(this.$button);

        // check for variations
        if (this.is_variable_product()) {
            if (!this.variable_product_selected()) {
                this.disable_payment_button();
            } else {
                this.enable_payment_button();
            }
        }
    }

    /**
     * @return {[type]}
     */
    GPay.prototype.get_button = function () {
        return this.$button.find('button');
    }

    /**
     * @return {[type]}
     */
    GPay.prototype.start = function () {
        if (this.get_quantity() > 0) {
            this.add_to_cart().then(function () {
                wc_stripe.GooglePay.prototype.start.apply(this, arguments);
            }.bind(this))
        } else {
            this.submit_error(this.params.messages.invalid_amount);
        }
    }

    GPay.prototype.found_variation = function () {
        wc_stripe.ProductGateway.prototype.found_variation.apply(this, arguments);
        this.enable_payment_button();
    }

    new GPay();

}(jQuery, wc_stripe))