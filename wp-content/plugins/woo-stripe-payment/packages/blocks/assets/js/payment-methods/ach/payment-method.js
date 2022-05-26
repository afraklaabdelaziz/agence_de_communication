import {useState} from '@wordpress/element';
import {registerPaymentMethod} from '@woocommerce/blocks-registry';
import {getSettings, isTestMode} from '../util';
import {PaymentMethodLabel, PaymentMethod} from '../../components/checkout';
import SavedCardComponent from '../saved-card-component';
import {useCreateLinkToken, useInitializePlaid, useProcessPayment} from './hooks';
import {useProcessCheckoutError} from "../hooks";
import {__} from '@wordpress/i18n';

const getData = getSettings('stripe_ach_data');

const ACHPaymentContent = (
    {
        getData,
        eventRegistration,
        components,
        emitResponse,
        onSubmit,
        ...props
    }) => {
    const {responseTypes} = emitResponse;
    const {onPaymentProcessing, onCheckoutAfterProcessingWithError} = eventRegistration;
    const {ValidationInputError} = components;
    const [validationError, setValidationError] = useState(false);

    const linkToken = useCreateLinkToken({setValidationError});

    useProcessCheckoutError({
        responseTypes,
        subscriber: onCheckoutAfterProcessingWithError
    });

    const openLinkPopup = useInitializePlaid({
        getData,
        linkToken,
        onSubmit
    });

    useProcessPayment({
        openLinkPopup,
        onPaymentProcessing,
        responseTypes,
        paymentMethod: getData('name')
    });
    return (
        <>
            {isTestMode && <ACHTestModeCredentials/>}
            {validationError && <ValidationInputError errorMessage={validationError}/>}
        </>
    )
}

const ACHTestModeCredentials = () => {
    return (
        <div className='wc-stripe-blocks-ach__creds'>
            <label className='wc-stripe-blocks-ach__creds-label'>{__('Test Credentials', 'woo-stripe-payment')}</label>
            <div className='wc-stripe-blocks-ach__username'>
                <div>
                    <strong>{__('username', 'woo-stripe-payment')}</strong>: user_good
                </div>
                <div>
                    <strong>{__('password', 'woo-stripe-payment')}</strong>: pass_good
                </div>
                <div>
                    <strong>{__('pin', 'woo-stripe-payment')}</strong>: credential_good
                </div>
            </div>
        </div>
    );
}

registerPaymentMethod({
    name: getData('name'),
    label: <PaymentMethodLabel title={getData('title')}
                               paymentMethod={getData('name')}
                               icons={getData('icons')}/>,
    ariaLabel: 'ACH Payment',
    canMakePayment: ({cartTotals}) => cartTotals.currency_code === 'USD',
    content: <PaymentMethod
        getData={getData}
        content={ACHPaymentContent}/>,
    savedTokenComponent: <SavedCardComponent getData={getData}/>,
    edit: <ACHPaymentContent getData={getData}/>,
    placeOrderButtonLabel: getData('placeOrderButtonLabel'),
    supports: {
        showSavedCards: getData('showSavedCards'),
        showSaveOption: false,
        features: getData('features')
    }
})