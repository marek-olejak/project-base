import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { CartItemFragmentApi } from 'graphql/generated';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { AddToCartAction } from 'hooks/cart/useAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { MouseEventHandler, useRef } from 'react';

type CartListItemProps = {
    item: CartItemFragmentApi;
    listIndex: number;
    onItemRemove: MouseEventHandler<HTMLButtonElement>;
    onItemQuantityChange: AddToCartAction;
};

const TEST_IDENTIFIER = 'pages-cart-list-item-';

export const CartListItem: FC<CartListItemProps> = ({
    item: { product, quantity, uuid },
    listIndex,
    onItemRemove,
    onItemQuantityChange,
}) => {
    const itemCatnum = product.catalogNumber;

    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const spinboxRef = useRef<HTMLInputElement>(null);
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const productSlug = product.__typename === 'Variant' ? product.mainVariant!.slug : product.slug;

    const onChangeValueHandler = () => {
        if (timeoutRef.current === null) {
            timeoutRef.current = setUpdateTimeout();
        } else {
            clearTimeout(timeoutRef.current);
            timeoutRef.current = setUpdateTimeout();
        }
    };

    const setUpdateTimeout = () => {
        return setTimeout(() => {
            onItemQuantityChange(product.uuid, spinboxRef.current!.valueAsNumber, listIndex, true);
        }, 500);
    };

    return (
        <div
            className="relative flex flex-row flex-wrap items-center gap-4 border-b border-greyLighter py-5 vl:flex-nowrap"
            data-testid={TEST_IDENTIFIER + itemCatnum}
        >
            <div className="flex flex-1 basis-full pr-8 vl:basis-auto vl:pr-0">
                <div className="flex w-24 shrink-0" data-testid={TEST_IDENTIFIER + 'image'}>
                    <ExtendedNextLink href={productSlug} type="product" className="relative h-full w-full">
                        <Image
                            image={product.mainImage}
                            type="thumbnailExtraSmall"
                            alt={product.mainImage?.name || product.fullName}
                            className="h-14"
                        />
                    </ExtendedNextLink>
                </div>

                <div className="flex flex-col items-start gap-4 text-sm font-bold vl:flex-1 vl:flex-row vl:items-center">
                    <div className="h-full text-left vl:w-[16.875rem]" data-testid={TEST_IDENTIFIER + 'name'}>
                        <ExtendedNextLink
                            href={productSlug}
                            type="product"
                            className="text-sm font-bold uppercase leading-4 text-dark no-underline hover:text-dark hover:no-underline"
                        >
                            {product.fullName}
                        </ExtendedNextLink>

                        <div className="text-sm text-greyLight">
                            {t('Code')}: {product.catalogNumber}
                        </div>
                    </div>

                    <div className="block flex-1 vl:text-center" data-testid={TEST_IDENTIFIER + 'availability'}>
                        {product.availability.name}

                        {!!product.availableStoresCount && (
                            <span className="ml-1 inline font-normal vl:ml-0 vl:block">
                                {t('or immediately in {{ count }} stores', {
                                    count: product.availableStoresCount,
                                })}
                            </span>
                        )}
                    </div>
                </div>
            </div>

            <div className="flex w-28 items-center vl:w-36" data-testid={TEST_IDENTIFIER + 'spinbox'}>
                <Spinbox
                    id={uuid}
                    min={1}
                    max={product.stockQuantity}
                    step={1}
                    defaultValue={quantity}
                    ref={spinboxRef}
                    onChangeValueCallback={onChangeValueHandler}
                />
            </div>

            <div className="flex items-center justify-end text-sm vl:w-32" data-testid={TEST_IDENTIFIER + 'itemprice'}>
                {formatPrice(product.price.priceWithVat) + '\u00A0/\u00A0' + t('pc')}
            </div>

            <div
                className="ml-auto flex items-center justify-end text-sm text-primary lg:text-base vl:w-32"
                data-testid={TEST_IDENTIFIER + 'totalprice'}
            >
                {formatPrice(mapPriceForCalculations(product.price.priceWithVat) * quantity)}
            </div>

            <RemoveCartItemButton
                onItemRemove={onItemRemove}
                className="absolute right-0 top-5 flex items-center vl:static"
            />
        </div>
    );
};
