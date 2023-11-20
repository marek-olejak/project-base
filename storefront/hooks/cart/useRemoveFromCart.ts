import { CartFragmentApi, CartItemFragmentApi, useRemoveFromCartMutationApi } from 'graphql/generated';
import { onGtmRemoveFromCartEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmProductListNameType } from 'gtm/types/enums';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import { dispatchBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { usePersistStore } from 'store/usePersistStore';

export type RemoveFromCartHandler = (
    cartItem: CartItemFragmentApi,
    listIndex: number,
) => Promise<CartFragmentApi | undefined | null>;

export const useRemoveFromCart = (gtmProductListName: GtmProductListNameType): [RemoveFromCartHandler, boolean] => {
    const [{ fetching }, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { url, currencyCode } = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { fetchCart } = useCurrentCart();

    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    const removeItemFromCartAction = async (cartItem: CartItemFragmentApi, listIndex: number) => {
        const removeItemFromCartActionResult = await removeItemFromCart({
            input: { cartUuid, cartItemUuid: cartItem.uuid },
        });

        if (removeItemFromCartActionResult.error) {
            fetchCart({ requestPolicy: 'network-only' });
        }

        if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
            updateCartUuid(removeItemFromCartActionResult.data.RemoveFromCart.uuid);

            const absoluteEventValueWithoutVat =
                mapPriceForCalculations(cartItem.product.price.priceWithoutVat) * cartItem.quantity;
            const absoluteEventValueWithVat =
                mapPriceForCalculations(cartItem.product.price.priceWithVat) * cartItem.quantity;

            onGtmRemoveFromCartEventHandler(
                cartItem,
                currencyCode,
                absoluteEventValueWithoutVat,
                absoluteEventValueWithVat,
                listIndex,
                gtmProductListName,
                url,
            );

            dispatchBroadcastChannel('refetchCart');
        }

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return [removeItemFromCartAction, fetching];
};
