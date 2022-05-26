import {useEffect, useCallback} from '@wordpress/element';
import {ensureSuccessResponse, ensureErrorResponse, deleteFromCache} from "../../util";

export const useProcessPayment = (
    {
        openLinkPopup,
        onPaymentProcessing,
        responseTypes,
        paymentMethod

    }) => {

    useEffect(() => {
        const unsubscribe = onPaymentProcessing(async () => {
            try {
                // open the Plaid popup
                const result = await openLinkPopup();
                const {publicToken, metaData} = result;
                // remove the cached link token.
                deleteFromCache('linkToken');
                return ensureSuccessResponse(responseTypes, {
                    meta: {
                        paymentMethodData: {
                            [`${paymentMethod}_token_key`]: publicToken,
                            [`${paymentMethod}_metadata`]: JSON.stringify(metaData)
                        }
                    }
                });
            } catch (err) {
                return ensureErrorResponse(responseTypes, err);
            }
        });
        return () => unsubscribe();
    }, [
        onPaymentProcessing,
        responseTypes,
        openLinkPopup
    ]);
}