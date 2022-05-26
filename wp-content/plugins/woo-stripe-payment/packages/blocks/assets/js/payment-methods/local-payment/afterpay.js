import {useState, useEffect} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {getSettings, initStripe} from "../util";
import {LocalPaymentIntentContent} from './local-payment-method';
import {PaymentMethod} from "../../components/checkout";
import {canMakePayment} from "./local-payment-method";
import {AfterpayClearpayMessageElement, Elements} from "@stripe/react-stripe-js";
import {sprintf, __} from '@wordpress/i18n';
import {ExperimentalOrderMeta, TotalsWrapper} from '@woocommerce/blocks-checkout';
import {registerPlugin} from '@wordpress/plugins';

const getData = getSettings('stripe_afterpay_data');
let variablesHandler;
const setVariablesHandler = (handler) => {
    variablesHandler = handler;
}

const isAvailable = ({total, currency, needsShipping}) => {
    let available = false;
    const requiredParams = getData('requiredParams');
    const accountCountry = getData('accountCountry');
    const requiredParamObj = requiredParams.hasOwnProperty(currency) ? requiredParams[currency] : false;
    if (requiredParamObj) {
        available = accountCountry === requiredParamObj?.[0] && needsShipping && (total > requiredParamObj?.[1] && total < requiredParamObj?.[2]);
    }
    return available;
}

const PaymentMethodLabel = ({getData}) => {
    const [variables, setVariables] = useState({
        amount: getData('cartTotal'),
        currency: getData('currency'),
        isEligible: getData('msgOptions').isEligible
    });
    setVariablesHandler(setVariables);
    return (
        <Elements stripe={initStripe} options={getData('elementOptions')}>
            <div className='wc-stripe-blocks-afterpay__label'>
                <AfterpayClearpayMessageElement options={{
                    ...getData('msgOptions'),
                    ...{
                        amount: variables.amount,
                        currency: variables.currency,
                        isEligible: variables.isEligible
                    }
                }}/>
            </div>
        </Elements>
    );
}

const AfterpayPaymentMethod = ({content, billing, shippingData, ...props}) => {
    const Content = content;
    const {cartTotal, currency} = billing;
    const {needsShipping} = shippingData
    useEffect(() => {
        variablesHandler({
            amount: cartTotal.value,
            currency: currency.code,
            isEligible: needsShipping
        });
    }, [
        cartTotal.value,
        currency.code,
        needsShipping
    ]);
    return (
        <>
            {needsShipping &&
            <div className='wc-stripe-blocks-payment-method-content'>
                <div className="wc-stripe-blocks-afterpay-offsite__container">
                    <div className="wc-stripe-blocks-afterpay__offsite">
                        <img src={getData('offSiteSrc')}/>
                        <p>{sprintf(__('After clicking "%s", you will be redirected to Afterpay to complete your purchase securely.', 'woo-stripe-payment'), getData('placeOrderButtonLabel'))}</p>
                    </div>
                </div>
                <Content {...{...props, billing, shippingData}}/>
            </div>}
        </>
    );
}

const OrderItemMessaging = ({cart, extensions, context}) => {
    const {cartTotals, cartNeedsShipping: needsShipping} = cart;
    const {total_price, currency_code: currency} = cartTotals;
    const amount = parseInt(cartTotals.total_price);
    const total = parseInt(cartTotals.total_price) / (10 ** cartTotals.currency_minor_unit);
    if (!isAvailable({total, currency, needsShipping})) {
        return null;
    }
    return (
        <TotalsWrapper>
            <Elements stripe={initStripe} options={getData('elementOptions')}>
                <div className='wc-stripe-blocks-afterpay-totals__item wc-block-components-totals-item'>
                    <AfterpayClearpayMessageElement options={{
                        ...getData('msgOptions'),
                        ...{
                            amount,
                            currency,
                            isEligible: needsShipping
                        }
                    }}/>
                </div>
            </Elements>
        </TotalsWrapper>
    );
}

if (getData()) {
    registerPaymentMethod({
        name: getData('name'),
        label: <PaymentMethodLabel
            getData={getData}/>,
        ariaLabel: __('Afterpay', 'woo-stripe-payment'),
        placeOrderButtonLabel: getData('placeOrderButtonLabel'),
        canMakePayment: canMakePayment(getData, ({settings, cartTotals, cartNeedsShipping}) => {
            const {currency_code: currency, currency_minor_unit, total_price} = cartTotals;
            if (variablesHandler) {
                variablesHandler({
                    amount: parseInt(cartTotals.total_price),
                    currency,
                    isEligible: cartNeedsShipping
                });
            }
            const total = parseInt(total_price) / (10 ** currency_minor_unit);
            const available = isAvailable({total, currency, needsShipping: cartNeedsShipping});
            if (!available && !settings('hideIneligible')) {
                return true;
            }
            return available;
        }),
        content: <AfterpayPaymentMethod
            content={LocalPaymentIntentContent}
            getData={getData}
            confirmationMethod={'confirmAfterpayClearpayPayment'}/>,
        edit: <PaymentMethod content={LocalPaymentIntentContent} getData={getData}/>,
        supports: {
            showSavedCards: false,
            showSaveOption: false,
            features: getData('features')
        }
    });

    const render = () => {
        return (
            <ExperimentalOrderMeta>
                <OrderItemMessaging/>
            </ExperimentalOrderMeta>
        )
    }
    registerPlugin('wc-stripe', {
        render: render,
        scope: 'woocommerce-checkout'
    })
}