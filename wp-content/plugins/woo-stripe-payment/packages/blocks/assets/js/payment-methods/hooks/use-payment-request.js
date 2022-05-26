import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import {usePaymentEvents} from './use-payment-events';
import {getIntermediateAddress} from '../util';
import isShallowEqual from '@wordpress/is-shallow-equal';
import {
    getDisplayItems,
    getShippingOptions,
    getSelectedShippingOption,
    isFieldRequired,
    toCartAddress as mapToCartAddress
} from "../util";

const toCartAddress = mapToCartAddress();

export const usePaymentRequest = (
    {
        getData,
        onClose,
        stripe,
        billing,
        shippingData,
        eventRegistration,
        setPaymentMethod,
        exportedValues,
        canPay
    }) => {
    const {addPaymentEvent} = usePaymentEvents({
        billing,
        shippingData,
        eventRegistration
    });
    const {shippingAddress, needsShipping, shippingRates} = shippingData;
    const {billingData, cartTotalItems, currency, cartTotal} = billing;
    const [paymentRequest, setPaymentRequest] = useState(null);
    const paymentRequestOptions = useRef({});
    const currentShipping = useRef(shippingData)
    const currentBilling = useRef(billing);

    useEffect(() => {
        currentShipping.current = shippingData;
        currentBilling.current = billing;
    }, [shippingData]);

    useEffect(() => {
        if (stripe) {
            const options = {
                country: getData('countryCode'),
                currency: currency?.code.toLowerCase(),
                total: {
                    amount: cartTotal.value,
                    label: cartTotal.label,
                    pending: true
                },
                requestPayerName: true,
                requestPayerEmail: isFieldRequired('email', billingData.country),
                requestPayerPhone: isFieldRequired('phone', billingData.country),
                requestShipping: needsShipping,
                displayItems: getDisplayItems(cartTotalItems, currency)
            }
            if (options.requestShipping) {
                options.shippingOptions = getShippingOptions(shippingRates);
            }
            paymentRequestOptions.current = options;
            const paymentRequest = stripe.paymentRequest(paymentRequestOptions.current);
            paymentRequest.canMakePayment().then(result => {
                if (canPay(result)) {
                    setPaymentRequest(paymentRequest);
                } else {
                    setPaymentRequest(null);
                }
            });
        }
    }, [
        stripe,
        cartTotal.value,
        billingData.country,
        shippingRates,
        cartTotalItems,
        currency.code
    ]);

    useEffect(() => {
        if (paymentRequest) {
            if (paymentRequestOptions.current.requestShipping) {
                paymentRequest.on('shippingaddresschange', onShippingAddressChange);
                paymentRequest.on('shippingoptionchange', onShippingOptionChange);
            }
            paymentRequest.on('cancel', onClose);
            paymentRequest.on('paymentmethod', onPaymentMethodReceived);
        }
    }, [
        paymentRequest,
        onShippingAddressChange,
        onClose,
        onPaymentMethodReceived
    ]);

    const updatePaymentEvent = useCallback((event) => (success, {billing, shipping}) => {
        const {cartTotal, cartTotalItems, currency} = billing;
        const {shippingRates} = shipping;
        if (success) {
            event.updateWith({
                status: 'success',
                total: {
                    amount: cartTotal.value,
                    label: cartTotal.label,
                    pending: false
                },
                displayItems: getDisplayItems(cartTotalItems, currency),
                shippingOptions: getShippingOptions(shippingRates)
            });
        } else {
            event.updateWith({status: 'invalid_shipping_address'});
        }
    }, []);

    const onShippingAddressChange = useCallback(event => {
        const {shippingAddress} = event;
        const shipping = currentShipping.current;
        const intermediateAddress = toCartAddress(shippingAddress);
        shipping.setShippingAddress({...shipping.shippingAddress, ...intermediateAddress});
        const addressEqual = isShallowEqual(getIntermediateAddress(shipping.shippingAddress), intermediateAddress);
        addPaymentEvent('onShippingChanged', updatePaymentEvent(event), addressEqual);
    }, [addPaymentEvent]);

    const onShippingOptionChange = useCallback(event => {
        const {shippingOption} = event;
        const shipping = currentShipping.current;
        shipping.setSelectedRates(...getSelectedShippingOption(shippingOption.id));
        addPaymentEvent('onShippingChanged', updatePaymentEvent(event));
    }, [addPaymentEvent]);

    const onPaymentMethodReceived = useCallback((paymentResponse) => {
        const {paymentMethod, payerName = null, payerEmail = null, payerPhone = null} = paymentResponse;
        // set address data
        let billingData = {payerName, payerEmail, payerPhone};
        if (paymentMethod?.billing_details.address) {
            billingData = toCartAddress(paymentMethod.billing_details.address, billingData);
        }
        exportedValues.billingData = billingData;

        if (paymentResponse.shippingAddress) {
            exportedValues.shippingAddress = toCartAddress(paymentResponse.shippingAddress);
        }

        // set payment method
        setPaymentMethod(paymentMethod.id);
        paymentResponse.complete("success");
    }, []);

    return {paymentRequest};
}