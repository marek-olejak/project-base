import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { getInternationalizedStaticUrls } from 'helpers/staticUrls/getInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

type AbandonedCartPageProps = { cartUuid?: string };

const AbandonedCartPage: FC<AbandonedCartPageProps> = ({ cartUuid }) => {
    const router = useRouter();
    const { url } = useDomainConfig();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    useEffect(() => {
        if (typeof cartUuid === 'string') {
            updateCartUuid(cartUuid);
        }
        router.replace(getInternationalizedStaticUrls(['/cart'], url)[0]);
    }, [cartUuid, router, updateCartUuid, url]);

    return null;
};

export const getServerSideProps = getServerSidePropsWrapper(() => async (context) => ({
    props: { cartUuid: context.params?.cartUuid },
}));

export default AbandonedCartPage;
