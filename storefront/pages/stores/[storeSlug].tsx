import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoreDetailContent } from 'components/Pages/StoreDetail/StoreDetailContent';
import {
    useStoreDetailQuery,
    TypeStoreDetailQuery,
    TypeStoreDetailQueryVariables,
    StoreDetailQueryDocument,
} from 'graphql/requests/stores/queries/StoreDetailQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { isRedirectedFromSsr } from 'utils/isRedirectedFromSsr';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const StoreDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: storeDetailData, fetching: isStoreFetching }] = useStoreDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const pageViewEvent = useGtmFriendlyPageViewEvent(storeDetailData?.store);
    useGtmPageViewEvent(pageViewEvent, isStoreFetching);

    return (
        <CommonLayout
            breadcrumbs={storeDetailData?.store?.breadcrumb}
            canonicalQueryParams={[]}
            isFetchingData={isStoreFetching}
            title={storeDetailData?.store?.storeName}
        >
            {!!storeDetailData?.store && <StoreDetailContent store={storeDetailData.store} />}
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
                const storeResponse: OperationResult<TypeStoreDetailQuery, TypeStoreDetailQueryVariables> =
                    await client!
                        .query(StoreDetailQueryDocument, {
                            urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                        })
                        .toPromise();

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    storeResponse.error?.graphQLErrors,
                    storeResponse.data?.store,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
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
