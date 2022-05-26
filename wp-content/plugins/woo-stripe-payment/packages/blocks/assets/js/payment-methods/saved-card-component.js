import {useEffect, useCallback} from '@wordpress/element';
import {initStripe as loadStripe, getSettings, handleCardAction} from '@paymentplugins/stripe/util';

const SavedCardComponent = (
    {
        eventRegistration,
        emitResponse,
        getData
    }) => {
    const {onCheckoutAfterProcessingWithSuccess} = eventRegistration;
    const {responseTypes} = emitResponse;
    const handleSuccessResult = useCallback(async ({redirectUrl}) => {
        const stripe = await loadStripe;
        return await handleCardAction({redirectUrl, getData, stripe, responseTypes});
    }, [onCheckoutAfterProcessingWithSuccess]);

    useEffect(() => {
        const unsubscribeOnCheckoutAfterProcessingWithSuccess = onCheckoutAfterProcessingWithSuccess(handleSuccessResult);
        return () => unsubscribeOnCheckoutAfterProcessingWithSuccess();
    }, [
        onCheckoutAfterProcessingWithSuccess
    ]);
    return null;
}

export default SavedCardComponent;
