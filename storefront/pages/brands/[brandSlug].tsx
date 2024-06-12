import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BrandDetailContent } from 'components/Pages/BrandDetail/BrandDetailContent';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    useBrandDetailQuery,
    TypeBrandDetailQuery,
    TypeBrandDetailQueryVariables,
    BrandDetailQueryDocument,
} from 'graphql/requests/brands/queries/BrandDetailQuery.generated';
import {
    TypeBrandProductsQuery,
    TypeBrandProductsQueryVariables,
    BrandProductsQueryDocument,
} from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getMappedProductFilter } from 'utils/filterOptions/getMappedProductFilter';
import { mapParametersFilter } from 'utils/filterOptions/mapParametersFilter';
import { isRedirectedFromSsr } from 'utils/isRedirectedFromSsr';
import { getRedirectWithOffsetPage } from 'utils/loadMore/getRedirectWithOffsetPage';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { getProductListSortFromUrlQuery } from 'utils/parsing/getProductListSortFromUrlQuery';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';

const BrandDetailPage: NextPage = () => {
    const router = useRouter();
    const currentFilter = useCurrentFilterQuery();
    const currentSort = useCurrentSortQuery();

    const [{ data: brandDetailData, fetching: isBrandFetching }] = useBrandDetailQuery({
        variables: {
            urlSlug: getSlugFromUrl(router.asPath),
            orderingMode: currentSort,
            filter: mapParametersFilter(currentFilter),
        },
    });

    const seoTitle = useSeoTitleWithPagination(
        brandDetailData?.brand?.products.totalCount,
        brandDetailData?.brand?.name,
        brandDetailData?.brand?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(brandDetailData?.brand);
    useGtmPageViewEvent(pageViewEvent, isBrandFetching);

    return (
        <CommonLayout
            breadcrumbs={brandDetailData?.brand?.breadcrumb}
            description={brandDetailData?.brand?.seoMetaDescription}
            hreflangLinks={brandDetailData?.brand?.hreflangLinks}
            isFetchingData={!currentFilter && isBrandFetching && !brandDetailData}
            title={seoTitle}
        >
            {!!brandDetailData?.brand && <BrandDetailContent brand={brandDetailData.brand} />}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 0);
            const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 1);
            const redirect = getRedirectWithOffsetPage(page, loadMore, urlSlug, context.query);

            if (redirect) {
                return redirect;
            }

            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            if (isRedirectedFromSsr(context.req.headers)) {
                const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
                const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);

                const brandDetailResponsePromise = client!
                    .query<TypeBrandDetailQuery, TypeBrandDetailQueryVariables>(BrandDetailQueryDocument, {
                        urlSlug,
                        orderingMode,
                        filter,
                    })
                    .toPromise();

                const brandProductsResponsePromise = client!
                    .query<TypeBrandProductsQuery, TypeBrandProductsQueryVariables>(BrandProductsQueryDocument, {
                        endCursor: getEndCursor(page),
                        orderingMode,
                        filter,
                        urlSlug,
                        pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                    })
                    .toPromise();

                const [brandDetailResponse] = await Promise.all([
                    brandDetailResponsePromise,
                    brandProductsResponsePromise,
                ]);

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    brandDetailResponse.error?.graphQLErrors,
                    brandDetailResponse.data?.brand,
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

export default BrandDetailPage;
