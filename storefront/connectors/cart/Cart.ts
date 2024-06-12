import { TypeCartItemModificationsFragment } from 'graphql/requests/cart/fragments/CartItemModificationsFragment.generated';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { TypeCartPaymentModificationsFragment } from 'graphql/requests/cart/fragments/CartPaymentModificationsFragment.generated';
import { TypeCartPromoCodeModificationsFragment } from 'graphql/requests/cart/fragments/CartPromoCodeModificationsFragment.generated';
import { TypeCartTransportModificationsFragment } from 'graphql/requests/cart/fragments/CartTransportModificationsFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { Translate } from 'next-translate';
import { ChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';

export const handleCartModifications = (
    cartModifications: TypeCartModificationsFragment,
    t: Translate,
    changePaymentInCart: ChangePaymentInCart,
): void => {
    handleRemovedProductFromEshopModifications(cartModifications.someProductWasRemovedFromEshop, t);
    handleCartTransportModifications(cartModifications.transportModifications, t, changePaymentInCart);
    handleCartPaymentModifications(cartModifications.paymentModifications, t);
    handleCartItemModifications(cartModifications.itemModifications, t);
    handleCartPromoCodeModifications(cartModifications.promoCodeModifications, t);
};

const handleRemovedProductFromEshopModifications = (someProductWasRemovedFromEshop: boolean, t: Translate): void => {
    if (someProductWasRemovedFromEshop) {
        showInfoMessage(
            t('Some product was removed from e-shop and your cart was recalculated.'),
            GtmMessageOriginType.cart,
        );
    }
};

const handleCartTransportModifications = (
    transportModifications: TypeCartTransportModificationsFragment,
    t: Translate,
    changePaymentInCart: ChangePaymentInCart,
): void => {
    if (transportModifications.transportPriceChanged) {
        showInfoMessage(t('The price of the transport you selected has changed.'), GtmMessageOriginType.cart);
    }
    if (transportModifications.transportUnavailable) {
        changePaymentInCart(null, null);
        showInfoMessage(t('The transport you selected is no longer available.'), GtmMessageOriginType.cart);
        showInfoMessage(t('Your transport and payment selection has been removed.'), GtmMessageOriginType.cart);
    }
    if (transportModifications.transportWeightLimitExceeded) {
        changePaymentInCart(null, null);
        showInfoMessage(t('You have exceeded the weight limit of the selected transport.'), GtmMessageOriginType.cart);
        showInfoMessage(t('Your transport and payment selection has been removed.'), GtmMessageOriginType.cart);
    }
};

const handleCartPaymentModifications = (
    paymentModifications: TypeCartPaymentModificationsFragment,
    t: Translate,
): void => {
    if (paymentModifications.paymentPriceChanged) {
        showInfoMessage(t('The price of the payment you selected has changed.'), GtmMessageOriginType.cart);
    }
    if (paymentModifications.paymentUnavailable) {
        showInfoMessage(t('The payment you selected is no longer available.'), GtmMessageOriginType.cart);
    }
};

const handleCartItemModifications = (itemModifications: TypeCartItemModificationsFragment, t: Translate): void => {
    for (const cartItemWithChangedQuantity of itemModifications.cartItemsWithChangedQuantity) {
        showInfoMessage(
            t('The quantity of item {{ itemName }} has changed.', {
                itemName: cartItemWithChangedQuantity.product.fullName,
            }),
            GtmMessageOriginType.cart,
        );
    }
    for (const cartItemWithModifiedPrice of itemModifications.cartItemsWithModifiedPrice) {
        showInfoMessage(
            t('The price of item {{ itemName }} has changed.', {
                itemName: cartItemWithModifiedPrice.product.fullName,
            }),
            GtmMessageOriginType.cart,
        );
    }
    for (const soldOutCartItem of itemModifications.noLongerAvailableCartItemsDueToQuantity) {
        showInfoMessage(
            t('Item {{ itemName }} has been sold out.', { itemName: soldOutCartItem.product.fullName }),
            GtmMessageOriginType.cart,
        );
    }
    for (const nonListableCartItem of itemModifications.noLongerListableCartItems) {
        showInfoMessage(
            t('Item {{ itemName }} can no longer be bought.', { itemName: nonListableCartItem.product.fullName }),
            GtmMessageOriginType.cart,
        );
    }
};

const handleCartPromoCodeModifications = (
    promoCodeModifications: TypeCartPromoCodeModificationsFragment,
    t: Translate,
): void => {
    for (const nonApplicablePromoCode of promoCodeModifications.noLongerApplicablePromoCode) {
        showInfoMessage(
            t('The promo code {{ promoCode }} is no longer applicable.', { promoCode: nonApplicablePromoCode }),
            GtmMessageOriginType.cart,
        );
    }
};
