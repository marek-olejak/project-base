import { openHeaderCartByHovering, removeFirstProductFromHeaderCart } from './cartSupport';
import { products } from 'fixtures/demodata';
import { initializePersistStoreInLocalStorageToDefaultValues, takeSnapshotAndCompare } from 'support';
import { TIDs } from 'tids';

describe('Cart in header tests', () => {
    beforeEach(() => {
        initializePersistStoreInLocalStorageToDefaultValues();
        cy.addProductToCartForTest(products.helloKitty.uuid, 2).then((cart) =>
            cy.storeCartUuidInLocalStorage(cart.uuid),
        );
        cy.addProductToCartForTest(products.philips32PFL4308.uuid);
        cy.visitAndWaitForStableAndInteractiveDOM('/');
    });

    it('should remove products from cart using cart in header and then display empty cart message', function () {
        openHeaderCartByHovering();
        removeFirstProductFromHeaderCart();
        takeSnapshotAndCompare(
            this.test?.title,
            'after first remove',
            {
                capture: 'viewport',
                wait: 0,
                blackout: [
                    { tid: TIDs.banners_slider, zIndex: 5999 },
                    { tid: TIDs.simple_navigation_image },
                    { tid: TIDs.header_cart_list_item_image, shouldNotOffset: true },
                ],
            },
            openHeaderCartByHovering,
        );

        openHeaderCartByHovering();
        removeFirstProductFromHeaderCart();
        openHeaderCartByHovering();
        takeSnapshotAndCompare(this.test?.title, 'after second remove', {
            capture: 'viewport',
            wait: 2000,
            blackout: [{ tid: TIDs.banners_slider, zIndex: 5999 }, { tid: TIDs.simple_navigation_image }],
        });
    });
});
