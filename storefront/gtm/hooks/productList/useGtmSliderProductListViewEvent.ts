import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useGtmContext } from 'gtm/context/useGtmContext';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmProductListViewEvent } from 'gtm/factories/getGtmProductListViewEvent';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';
import { useEffect, useRef } from 'react';

export const useGtmSliderProductListViewEvent = (
    products: ListedProductFragment[] | undefined,
    gtmProuctListName: GtmProductListNameType,
): void => {
    const wasViewedRef = useRef(false);
    const { url } = useDomainConfig();
    const { didPageViewRun } = useGtmContext();

    useEffect(() => {
        if (didPageViewRun && products?.length && !wasViewedRef.current) {
            wasViewedRef.current = true;
            gtmSafePushEvent(getGtmProductListViewEvent(products, gtmProuctListName, 1, 0, url));
        }
    }, [gtmProuctListName, products, url, didPageViewRun]);
};
