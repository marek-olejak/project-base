import { TransportAndPaymentSelect } from './TransportAndPaymentSelect/TransportAndPaymentSelect';
import {
    getTransportAndPaymentValidationMessages,
    useLoadTransportAndPaymentFromLastOrder,
    useTransportAndPaymentPageNavigation,
} from './utils';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { useTransportsQuery } from 'graphql/requests/transports/queries/TransportsQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';
import { useChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { useChangeTransportInCart } from 'utils/cart/useChangeTransportInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';

export const TransportAndPaymentContent: FC = () => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { transport, pickupPlace, payment, paymentGoPayBankSwift } = useCurrentCart();

    const [changeTransportInCart, isTransportSelectionLoading] = useChangeTransportInCart();
    const [changePaymentInCart, isPaymentSelectionLoading] = useChangePaymentInCart();
    const [{ data: transportsData, fetching: areTransportsLoading }] = useTransportsQuery({
        variables: { cartUuid },
        requestPolicy: 'network-only',
    });

    const [isLoadingTransportAndPaymentFromLastOrder, lastOrderPickupPlace] = useLoadTransportAndPaymentFromLastOrder(
        changeTransportInCart,
        changePaymentInCart,
    );
    const validationMessages = getTransportAndPaymentValidationMessages(
        transport,
        pickupPlace,
        payment,
        paymentGoPayBankSwift,
        t,
    );
    const { goToPreviousStepFromTransportAndPaymentPage, goToNextStepFromTransportAndPaymentPage } =
        useTransportAndPaymentPageNavigation(validationMessages);

    return (
        <OrderLayout
            isFetchingData={isLoadingTransportAndPaymentFromLastOrder || areTransportsLoading}
            page="transport-and-payment"
        >
            <OrderContentWrapper
                activeStep={2}
                isTransportOrPaymentLoading={isTransportSelectionLoading || isPaymentSelectionLoading}
            >
                {!!transportsData?.transports.length && (
                    <TransportAndPaymentSelect
                        changePaymentInCart={changePaymentInCart}
                        changeTransportInCart={changeTransportInCart}
                        isTransportSelectionLoading={isTransportSelectionLoading}
                        lastOrderPickupPlace={lastOrderPickupPlace}
                        transports={transportsData.transports}
                    />
                )}

                <OrderAction
                    withGapBottom
                    withGapTop
                    backStepClickHandler={goToPreviousStepFromTransportAndPaymentPage}
                    buttonBack={t('Back')}
                    buttonNext={t('Contact information')}
                    isLoading={(isTransportSelectionLoading || isPaymentSelectionLoading) && !!transport && !!payment}
                    nextStepClickHandler={goToNextStepFromTransportAndPaymentPage}
                    hasDisabledLook={
                        hasValidationErrors(validationMessages) ||
                        isTransportSelectionLoading ||
                        isPaymentSelectionLoading
                    }
                />
            </OrderContentWrapper>
        </OrderLayout>
    );
};
