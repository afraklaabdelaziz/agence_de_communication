import {useState, useEffect, useCallback, useMemo, useRef} from '@wordpress/element';
import isShallowEqual from "@wordpress/is-shallow-equal";
import {
    getErrorMessage,
    getSelectedShippingOption,
    getBillingDetailsFromAddress,
    isAddressValid,
    isEmpty,
    StripeError,
    getIntermediateAddress
} from "../../util";
import {useStripe} from "@stripe/react-stripe-js";
import {getPaymentRequestUpdate, toCartAddress} from "../util";
import {__} from "@wordpress/i18n";
import {usePaymentEvents} from "../../hooks";

export const usePaymentsClient = (
    {
        merchantInfo,
        paymentRequest,
        billing,
        shippingData,
        eventRegistration,
        canMakePayment,
        setErrorMessage,
        setPaymentMethod,
        exportedValues,
        onClick,
        onClose,
        getData
    }) => {
    const {environment} = getData();
    const [paymentsClient, setPaymentsClient] = useState();
    const [button, setButton] = useState(null);
    const currentBilling = useRef(billing);
    const currentShipping = useRef(shippingData);
    const stripe = useStripe();
    const {addPaymentEvent} = usePaymentEvents({
        billing,
        shippingData,
        eventRegistration
    });
    useEffect(() => {
        currentBilling.current = billing;
        currentShipping.current = shippingData;
    });

    const setAddressData = useCallback((paymentData) => {
        if (paymentData?.paymentMethodData?.info?.billingAddress) {
            let billingAddress = paymentData.paymentMethodData.info.billingAddress;
            if (isAddressValid(currentBilling.current.billingData, ['phone', 'email']) && isEmpty(currentBilling.current.billingData?.phone)) {
                billingAddress = {phoneNumber: billingAddress.phoneNumber};
            }
            exportedValues.billingData = currentBilling.current.billingData = toCartAddress(billingAddress, {email: paymentData.email});
        }
        if (paymentData?.shippingAddress) {
            exportedValues.shippingAddress = toCartAddress(paymentData.shippingAddress);
        }
    }, [exportedValues, paymentRequest]);

    const removeButton = useCallback((parentElement) => {
        while (parentElement.firstChild) {
            parentElement.removeChild(parentElement.firstChild);
        }
    }, [button]);
    const handleClick = useCallback(async () => {
        onClick();
        try {
            let paymentData = await paymentsClient.loadPaymentData(paymentRequest);

            // set the address data so it can be used during the checkout process
            setAddressData(paymentData);

            const data = JSON.parse(paymentData.paymentMethodData.tokenizationData.token);

            let result = await stripe.createPaymentMethod({
                type: 'card',
                card: {token: data.id},
                billing_details: getBillingDetailsFromAddress(currentBilling.current.billingData)
            });

            if (result.error) {
                throw new StripeError(result.error);
            }

            setPaymentMethod(result.paymentMethod.id);
        } catch (err) {
            if (err?.statusCode === "CANCELED") {
                onClose();
            } else {
                console.log(getErrorMessage(err));
                setErrorMessage(getErrorMessage(err));
            }
        }
    }, [
        stripe,
        paymentsClient,
        onClick
    ]);

    const createButton = useCallback(async () => {
        try {
            if (paymentsClient && !button && stripe) {
                await canMakePayment;
                setButton(paymentsClient.createButton({
                    onClick: handleClick,
                    ...getData('buttonStyle')
                }));
            }
        } catch (err) {
            console.log(err);
        }
    }, [
        stripe,
        button,
        paymentsClient,
        handleClick
    ]);

    const paymentOptions = useMemo(() => {
        let options = {
            environment,
            merchantInfo,
            paymentDataCallbacks: {
                onPaymentAuthorized: () => Promise.resolve({transactionState: "SUCCESS"})
            }
        }
        if (paymentRequest.shippingAddressRequired) {
            options.paymentDataCallbacks.onPaymentDataChanged = (paymentData) => {
                return new Promise((resolve, reject) => {
                    const shipping = currentShipping.current;
                    const {shippingAddress: address, shippingOptionData} = paymentData;
                    const intermediateAddress = toCartAddress(address);
                    // pass the Promise resolve to a ref so it persists beyond the re-render
                    const selectedRates = getSelectedShippingOption(shippingOptionData.id);
                    const addressEqual = isShallowEqual(getIntermediateAddress(shipping.shippingAddress), intermediateAddress);
                    const shippingEqual = isShallowEqual(shipping.selectedRates, {
                        [selectedRates[1]]: selectedRates[0]
                    });
                    addPaymentEvent('onShippingChanged', (success, {billing, shipping}) => {
                        if (success) {
                            resolve(getPaymentRequestUpdate({
                                billing,
                                shippingData: {
                                    needsShipping: true,
                                    shippingRates: shipping.shippingRates
                                },
                                processingCountry: getData('processingCountry'),
                                totalPriceLabel: getData('totalPriceLabel')
                            }))
                        } else {
                            resolve({
                                error: {
                                    reason: 'SHIPPING_ADDRESS_UNSERVICEABLE',
                                    message: __('Your shipping address is not serviceable.', 'woo-stripe-payment'),
                                    intent: 'SHIPPING_ADDRESS'
                                }
                            });
                        }
                    }, addressEqual && shippingEqual);
                    currentShipping.current.setShippingAddress({...currentShipping.current.shippingAddress, ...intermediateAddress});
                    if (shippingOptionData.id !== 'shipping_option_unselected') {
                        currentShipping.current.setSelectedRates(...selectedRates);
                    }
                })
            }
        }
        return options;
    }, [paymentRequest]);

    useEffect(() => {
        setPaymentsClient(new google.payments.api.PaymentsClient(paymentOptions));
    }, [paymentOptions]);

    useEffect(() => {
        createButton();
    }, [createButton])

    return {
        button,
        removeButton
    };
}