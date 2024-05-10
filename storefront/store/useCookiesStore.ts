import { CookiesStoreContext } from 'components/providers/CookiesStoreProvider';
import { useContext } from 'react';
import { CookiesStore } from 'utils/cookies/cookiesStore';
import { useStore } from 'zustand';

export const useCookiesStore = <T>(selector: (store: CookiesStore) => T): T => {
    const cookiesStoreContext = useContext(CookiesStoreContext);

    if (!cookiesStoreContext) {
        throw new Error(`useCookiesStore must be use within CookiesStoreProvider`);
    }

    return useStore(cookiesStoreContext, selector);
};
