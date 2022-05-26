import {useEffect, useState, useCallback, useRef} from '@wordpress/element';
import {useStripe} from '@stripe/react-stripe-js';
import {
    ensureSuccessResponse,
    ensureErrorResponse,
    getBillingDetailsFromAddress,
    StripeError
} from '../util';

export const useProcessPaymentIntent = (
    {
        getData,
        billing,
        shippingData,
        onPaymentProcessing,
        emitResponse,
        error,
        onSubmit,
        activePaymentMethod,
        paymentType = 'card',
        setupIntent = null,
        removeSetupIntent = null,
        savePaymentMethod = false,
        exportedValues = {},
        getPaymentMethodArgs = () => ({})
    }) => {
    const {billingData} = billing;
    const {shippingAddress} = shippingData;
    const {responseTypes} = emitResponse;
    const [paymentMethod, setPaymentMethod] = useState(null);
    const stripe = useStripe();
    const currentPaymentMethodArgs = useRef(getPaymentMethodArgs);
    const paymentMethodData = useRef({});
    useEffect(() => {
        currentPaymentMethodArgs.current = getPaymentMethodArgs;
    }, [getPaymentMethodArgs]);

    const addPaymentMethodData = useCallback((data) => {
        paymentMethodData.current = {...paymentMethodData.current, ...data};
    }, []);

    const getCreatePaymentMethodArgs = useCallback(() => {
        const args = {
            type: paymentType,
            billing_details: getBillingDetailsFromAddress(exportedValues?.billingData ? exportedValues.billingData : billingData)
        }
        return {...args, ...currentPaymentMethodArgs.current()};
    }, [billingData, paymentType, getPaymentMethodArgs]);

    const getSuccessResponse = useCallback((paymentMethodId, savePaymentMethod) => {
        const response = {
            meta: {
                paymentMethodData: {
                    [`${getData('name')}_token_key`]: paymentMethodId,
                    [`${getData('name')}_save_source_key`]: savePaymentMethod,
                    ...paymentMethodData.current
                }
            }
        }
        if (exportedValues?.billingData) {
            response.meta.billingData = exportedValues.billingData;
        }
        if (exportedValues?.shippingAddress) {
            response.meta.shippingData = {address: exportedValues.shippingAddress};
        }
        return response;
    }, [billingData, shippingAddress]);

    useEffect(() => {
        if (paymentMethod && typeof paymentMethod === 'string') {
            onSubmit();
        }
    }, [paymentMethod, onSubmit]);

    useEffect(() => {
        const unsubscribeProcessingPayment = onPaymentProcessing(async () => {
            if (activePaymentMethod !== getData('name')) {
                return null;
            }
            let [result, paymentMethodId] = [null, null];
            try {
                if (error) {
                    throw new StripeError(error);
                }
                if (setupIntent) {
                    result = await stripe.confirmCardSetup(setupIntent.client_secret, {
                        payment_method: getCreatePaymentMethodArgs()
                    });
                    if (result.error) {
                        throw new StripeError(result.error);
                    }
                    paymentMethodId = result.setupIntent.payment_method;
                    removeSetupIntent();
                } else {
                    // payment method has already been created.
                    if (paymentMethod) {
                        paymentMethodId = paymentMethod;
                    } else {
                        //create the payment method
                        result = await stripe.createPaymentMethod(getCreatePaymentMethodArgs());
                        if (result.error) {
                            throw new StripeError(result.error);
                        }
                        paymentMethodId = result.paymentMethod.id;
                    }
                }
                return ensureSuccessResponse(responseTypes, getSuccessResponse(paymentMethodId, savePaymentMethod));
            } catch (e) {
                console.log(e);
                setPaymentMethod(null);
                return ensureErrorResponse(responseTypes, e.error);
            }

        });
        return () => unsubscribeProcessingPayment();
    }, [
        paymentMethod,
        billingData,
        onPaymentProcessing,
        stripe,
        setupIntent,
        activePaymentMethod,
        savePaymentMethod
    ]);
    return {
        setPaymentMethod,
        getCreatePaymentMethodArgs,
        addPaymentMethodData
    };
}