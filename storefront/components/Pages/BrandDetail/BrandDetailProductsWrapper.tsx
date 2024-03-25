import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { BrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { BrandProductsQueryDocument } from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { useProductsData } from 'helpers/loadMore/useProductsData';
import { getMappedProducts } from 'helpers/mappers/products';
import { RefObject } from 'react';

type BrandDetailProductsWrapperProps = {
    brand: BrandDetailFragment;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const BrandDetailProductsWrapper: FC<BrandDetailProductsWrapperProps> = ({
    brand,
    paginationScrollTargetRef,
}) => {
    const [brandProductsData, hasNextPage, fetching, loadMoreFetching] = useProductsData(
        BrandProductsQueryDocument,
        brand.products.totalCount,
    );
    const listedBrandProducts = getMappedProducts(brandProductsData);

    useGtmPaginatedProductListViewEvent(listedBrandProducts, GtmProductListNameType.brand_detail);

    return (
        <>
            <ProductsList
                fetching={fetching}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.brand_detail}
                loadMoreFetching={loadMoreFetching}
                products={listedBrandProducts}
            />
            <Pagination
                isWithLoadMore
                hasNextPage={hasNextPage}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={brand.products.totalCount}
            />
        </>
    );
};
