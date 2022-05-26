import {useState, useEffect, useMemo} from '@wordpress/element';
import {BASE_PAYMENT_REQUEST, BASE_PAYMENT_METHOD} from "../constants";
import {isEmpty, isFieldRequired} from "../../util";
import {getTransactionInfo, getShippingOptionParameters} from "../util";

export const usePaymentRequest = ({getData, publishableKey, merchantInfo, billing, shippingData}) => {
    const {billingData} = billing;
    const {shippingRates} = shippingData;
    const {processingCountry, totalPriceLabel} = getData();

    const paymentRequest = useMemo(() => {
        let options = {
            ...{
                emailRequired: isEmpty(billingData.email),
                merchantInfo,
                allowedPaymentMethods: [{
                    ...{
                        type: 'CARD',
                        tokenizationSpecification: {
                            type: "PAYMENT_GATEWAY",
                            parameters: {
                                gateway: 'stripe',
                                "stripe:version": "2018-10-31",
                                "stripe:publishableKey": publishableKey
                            }
                        }
                    }, ...BASE_PAYMENT_METHOD
                }],
                shippingAddressRequired: shippingData.needsShipping,
                transactionInfo: getTransactionInfo({
                    billing,
                    processingCountry,
                    totalPriceLabel
                }),
                callbackIntents: ['PAYMENT_AUTHORIZATION']
            }, ...BASE_PAYMENT_REQUEST
        };
        options.allowedPaymentMethods[0].parameters.billingAddressRequired = true;
        options.allowedPaymentMethods[0].parameters.billingAddressParameters = {
            format: 'FULL',
            phoneNumberRequired: isFieldRequired('phone', billingData.country) && isEmpty(billingData.phone)
        };
        if (options.shippingAddressRequired) {
            options.callbackIntents = [...options.callbackIntents, ...['SHIPPING_ADDRESS', 'SHIPPING_OPTION']];
            options.shippingOptionRequired = true;
            const shippingOptionParameters = getShippingOptionParameters(shippingRates);
            if (shippingOptionParameters.shippingOptions.length > 0) {
                options = {...options, shippingOptionParameters};
            }
        }
        return options;
    }, [
        billing.cartTotal,
        billing.cartTotalItems,
        billingData,
        shippingData
    ]);
    return paymentRequest;
}