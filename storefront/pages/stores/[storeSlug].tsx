import { SkeletonPageStore } from 'components/Blocks/Skeleton/SkeletonPageStore';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoreDetailContent } from 'components/Pages/StoreDetail/StoreDetailContent';
import {
    StoreDetailQueryApi,
    StoreDetailQueryDocumentApi,
    StoreDetailQueryVariablesApi,
    useStoreDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';

const StoreDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: storeDetailData, fetching }] = useStoreDetailQueryApi({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const pageViewEvent = useGtmFriendlyPageViewEvent(storeDetailData?.store);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            breadcrumbs={storeDetailData?.store?.breadcrumb}
            canonicalQueryParams={[]}
            title={storeDetailData?.store?.storeName}
        >
            {!!storeDetailData?.store && !fetching ? (
                <StoreDetailContent store={storeDetailData.store} />
            ) : (
                <SkeletonPageStore />
            )}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            if (isRedirectedFromSsr(context.req.headers)) {
                const storeResponse: OperationResult<StoreDetailQueryApi, StoreDetailQueryVariablesApi> = await client!
                    .query(StoreDetailQueryDocumentApi, {
                        urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                    })
                    .toPromise();

                if ((!storeResponse.data || !storeResponse.data.store) && !(context.res.statusCode === 503)) {
                    return {
                        notFound: true,
                    };
                }
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                ssrExchange,
                domainConfig,
            });

            return initServerSideData;
        },
);

export default StoreDetailPage;
