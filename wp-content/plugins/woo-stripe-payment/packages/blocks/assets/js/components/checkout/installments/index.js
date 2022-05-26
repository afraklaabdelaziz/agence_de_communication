import {useState, useRef, useEffect} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import classnames from 'classnames';
import {StripeError, getRoute} from "../../../payment-methods/util";
import './style.scss';

export const Installments = (
    {
        paymentMethodName,
        stripe,
        getCreatePaymentMethodArgs,
        cardFormComplete = false,
        addPaymentMethodData
    }) => {
    const [installments, setInstallments] = useState(null);
    const [installment, setInstallment] = useState('');
    const [loading, setLoading] = useState(false);
    const onInstallmentSelected = (e) => {
        setInstallment(e.target.value);
        addPaymentMethodData({_stripe_installment_plan: e.target.value});
    }
    useEffect(async () => {
        if (cardFormComplete) {
            // fetch the installments
            try {
                setLoading(true);
                setInstallment('');
                let result = await stripe.createPaymentMethod(getCreatePaymentMethodArgs());
                if (result.error) {
                    throw new StripeError(result.error);
                }
                // fetch the installment plans
                result = await apiFetch({
                    url: getRoute('create/payment_intent'),
                    method: 'POST',
                    data: {payment_method_id: result.paymentMethod.id, payment_method: paymentMethodName}
                });
                setInstallments(result.installments);
                if (Object.keys(result.installments)?.length) {
                    setInstallment(Object.keys(result.installments)[0]);
                }
            } catch (error) {

            } finally {
                setLoading(false);
            }
        }
    }, [cardFormComplete]);

    return (
        <div className='wc-stripe-installments__container'>
            <label>
                {__('Pay in installments:', 'woo-stripe-payment')}
                <Loader loading={loading}/>
            </label>
            <InstallmentOptions
                installment={installment}
                onChange={onInstallmentSelected}
                installments={installments}
                isLoading={loading}/>
        </div>
    )
}

const InstallmentOptions = ({installment, installments, onChange, isLoading}) => {
    let OPTIONS = null;
    if (isLoading) {
        OPTIONS = <option value="" disabled>{__('Loading installments...', 'woo-stripe-payment')}</option>
    } else {
        if (installments === null) {
            OPTIONS = <option value="" disabled>{__('Fill out card form for eligibility.', 'woo-stripe-payment')}</option>
        } else {
            OPTIONS = Object.keys(installments).map(id => {
                return <option key={id} value={id} dangerouslySetInnerHTML={{__html: installments[id].text}}/>
            });
        }
    }
    return (
        <select value={installment} onChange={onChange} className={classnames({loading: isLoading})}>
            {OPTIONS}
        </select>
    );
}

const Loader = ({loading}) => {
    return (
        <div className="wc-stripe-installment-loader__container">
            {loading && <div className="wc-stripe-installment-loader">
                <div></div>
                <div></div>
                <div></div>
            </div>}
        </div>
    );
}
export default Installments;