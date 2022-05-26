import {useState, useEffect, useRef, useCallback} from '@wordpress/element';
import Plaid from '@plaid';
import {getErrorMessage} from "../../util";

export const useInitializePlaid = (
    {
        getData,
        linkToken
    }) => {
    const linkHandler = useRef(null);
    const resolvePopup = useRef(null);
    const openLinkPopup = useCallback(() => new Promise((resolve, reject) => {
        resolvePopup.current = {resolve, reject};
        linkHandler.current.open();
    }), []);

    // if the token exists, initialize Plaid's link handler
    useEffect(() => {
        if (linkToken) {
            linkHandler.current = Plaid.create({
                clientName: getData('clientName'),
                env: getData('plaidEnvironment'),
                product: ['auth'],
                token: linkToken,
                selectAccount: true,
                countryCodes: ['US'],
                onSuccess: (publicToken, metaData) => {
                    resolvePopup.current.resolve({publicToken, metaData});
                },
                onExit: (err) => {
                    resolvePopup.current.reject(err ? getErrorMessage(err.error_message) : false);
                }
            });
        }
    }, [linkToken]);

    return openLinkPopup;
}