import {useEffect, useState, useCallback, useMemo} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {
    initStripe as loadStripe,
    getSettings,
    isUserLoggedIn,
    cartContainsSubscription,
    cartContainsPreOrder
} from '../util';
import {Elements, CardElement, useStripe, useElements, CardNumberElement} from '@stripe/react-stripe-js';
import {PaymentMethodLabel, PaymentMethod, SavePaymentMethod} from '../../components/checkout';
import SavedCardComponent from '../saved-card-component';
import CustomCardForm from './components/custom-card-form';
import StripeCardForm from "./components/stripe-card-form";
import {Installments} from '../../components/checkout';
import {
    useProcessPaymentIntent,
    useAfterProcessingPayment,
    useSetupIntent,
    useStripeError
} from "../hooks";

const getData = getSettings('stripe_cc_data');

const displaySaveCard = (customerId) => {
    return isUserLoggedIn(customerId) && getData('saveCardEnabled') &&
        !cartContainsSubscription() && !cartContainsPreOrder()
}

const CreditCardContent = (props) => {
    const [error, setError] = useState(false);
    useEffect(() => {
        loadStripe.catch(error => {
            setError(error);
        })
    }, [setError]);
    if (error) {
        throw new Error(error);
    }
    return (
        <Elements stripe={loadStripe} options={getData('elementOptions')}>
            <CreditCardElement {...props}/>
        </Elements>
    );
};

const CreditCardElement = (
    {
        getData,
        billing,
        shippingData,
        emitResponse,
        eventRegistration,
        activePaymentMethod
    }) => {
    const [error, setError] = useStripeError();
    const [savePaymentMethod, setSavePaymentMethod] = useState(false);
    const [formComplete, setFormComplete] = useState(false);
    const onSavePaymentMethod = (checked) => setSavePaymentMethod(checked);
    const {onPaymentProcessing} = eventRegistration;
    const stripe = useStripe();
    const elements = useElements();
    const getPaymentMethodArgs = useCallback(() => {
        const elType = getData('customFormActive') ? CardNumberElement : CardElement;
        return {card: elements.getElement(elType)};
    }, [stripe, elements]);

    const {setupIntent, removeSetupIntent} = useSetupIntent({
        getData,
        cartTotal: billing.cartTotal,
        setError
    })

    const {getCreatePaymentMethodArgs, addPaymentMethodData} = useProcessPaymentIntent({
        getData,
        billing,
        shippingData,
        emitResponse,
        error,
        onPaymentProcessing,
        savePaymentMethod,
        setupIntent,
        removeSetupIntent,
        getPaymentMethodArgs,
        activePaymentMethod
    });
    useAfterProcessingPayment({
        getData,
        eventRegistration,
        responseTypes: emitResponse.responseTypes,
        activePaymentMethod,
        savePaymentMethod
    });

    const onChange = (event) => {
        if (event.error) {
            setError(event.error);
        } else {
            setError(false);
        }
    }
    const Tag = getData('customFormActive') ? CustomCardForm : StripeCardForm;
    return (
        <div className='wc-stripe-card-container'>
            <Tag {...{getData, billing, onChange}} onComplete={setFormComplete}/>
            {displaySaveCard(billing.customerId) &&
            <SavePaymentMethod label={getData('savePaymentMethodLabel')}
                               onChange={onSavePaymentMethod}
                               checked={savePaymentMethod}/>}
            {getData('installmentsActive') && <Installments
                paymentMethodName={getData('name')}
                stripe={stripe}
                cardFormComplete={formComplete}
                getCreatePaymentMethodArgs={getCreatePaymentMethodArgs}
                addPaymentMethodData={addPaymentMethodData}/>}
        </div>
    );
}

registerPaymentMethod({
    name: getData('name'),
    label: <PaymentMethodLabel
        title={getData('title')}
        paymentMethod={getData('name')}
        icons={getData('icons')}/>,
    ariaLabel: 'Credit Cards',
    canMakePayment: () => loadStripe,
    content: <PaymentMethod content={CreditCardContent} getData={getData}/>,
    savedTokenComponent: <SavedCardComponent getData={getData}/>,
    edit: <PaymentMethod content={CreditCardContent} getData={getData}/>,
    supports: {
        showSavedCards: getData('showSavedCards'),
        showSaveOption: false,
        features: getData('features')
    }
})