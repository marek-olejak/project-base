import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { Popup } from 'components/Layout/Popup/Popup';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

type ProductDetailAvailabilityProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailAvailability: FC<ProductDetailAvailabilityProps> = ({ product }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    if (!product.availableStoresCount) {
        return null;
    }

    return (
        <div
            className={twJoin(
                'mr-1 text-sm flex cursor-pointer items-center font-secondary',
                product.availability.status === TypeAvailabilityStatusEnum.InStock && 'text-availabilityInStock',
                product.availability.status === TypeAvailabilityStatusEnum.OutOfStock && 'text-availabilityOutOfStock',
            )}
            onClick={() =>
                updatePortalContent(
                    <Popup>
                        <ProductDetailAvailabilityList storeAvailabilities={product.storeAvailabilities} />
                    </Popup>,
                )
            }
        >
            {`${product.availability.name}. ${t('This item is available immediately in {{ count }} stores', {
                availability: product.availability.name,
                count: product.availableStoresCount,
            })}`}
        </div>
    );
};
