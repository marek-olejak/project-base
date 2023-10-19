import { ProductListItem } from './ProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { ListedProductFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useComparison } from 'hooks/comparison/useComparison';
import { useQueryParams } from 'hooks/useQueryParams';
import { useWishlist } from 'hooks/useWishlist';
import dynamic from 'next/dynamic';
import React, { RefObject } from 'react';

const ProductComparePopup = dynamic(() =>
    import('../ButtonsAction/ProductComparePopup').then((component) => component.ProductComparePopup),
);

type ProductsListProps = {
    products: ListedProductFragmentApi[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    ref?: RefObject<HTMLUListElement>;
    productRefs?: RefObject<HTMLLIElement>[];
    className?: string;
    classNameProduct?: string;
};

export const ProductsListContent: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    productRefs,
    className,
    classNameProduct,
    ref,
    dataTestId,
    children,
}) => {
    const { currentPage } = useQueryParams();
    const { isPopupCompareOpen, toggleProductInComparison, setIsPopupCompareOpen, isProductInComparison } =
        useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    return (
        <>
            <ul className={className} data-testid={dataTestId} ref={ref}>
                {products.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        className={classNameProduct}
                        gtmMessageOrigin={gtmMessageOrigin}
                        gtmProductListName={gtmProductListName}
                        isProductInComparison={isProductInComparison(product.uuid)}
                        isProductInWishlist={isProductInWishlist(product.uuid)}
                        listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                        product={product}
                        ref={productRefs?.[index]}
                        toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                        toggleProductInWishlist={() => toggleProductInWishlist(product.uuid)}
                    />
                ))}
                {children}
            </ul>

            {isPopupCompareOpen && <ProductComparePopup onCloseCallback={() => setIsPopupCompareOpen(false)} />}
        </>
    );
};
