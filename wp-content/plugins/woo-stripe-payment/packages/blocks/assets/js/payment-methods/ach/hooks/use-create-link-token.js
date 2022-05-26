import {useEffect, useState, useCallback} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {getRoute, getFromCache, storeInCache} from '../../util';

export const useCreateLinkToken = (
    {
        setValidationError
    }) => {
    const [linkToken, setLinkToken] = useState(false);

    const createToken = useCallback(async () => {
        try {
            const response = await apiFetch({
                url: getRoute('create/linkToken'),
                method: 'POST',
                data: {}
            });
            if (response.token) {
                storeInCache('linkToken', response.token);
                setLinkToken(response.token);
            }
        } catch (err) {
            setValidationError(err);
        }
    }, []);

    useEffect(() => {
        if (!linkToken) {
            const token = getFromCache('linkToken');
            if (token) {
                // cached token exist so use it
                setLinkToken(token);
            } else {
                // create the Plaid Link token
                createToken();
            }
        }
    }, [
        linkToken,
        setLinkToken
    ]);
    return linkToken;
}