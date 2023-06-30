import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/formMeta';
import { useEffect } from 'react';
import { Control, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';

export const useHandleContactInformationNonTextChanges = (
    control: Control<ContactInformation>,
    formMeta: ReturnType<typeof useContactInformationFormMeta>,
): void => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const [
        customerValue,
        countryValue,
        differentDeliveryAddressValue,
        deliveryCountryValue,
        newsletterSubscriptionValue,
    ] = useWatch({
        name: [
            formMeta.fields.customer.name,
            formMeta.fields.country.name,
            formMeta.fields.differentDeliveryAddress.name,
            formMeta.fields.deliveryCountry.name,
            formMeta.fields.newsletterSubscription.name,
        ],
        control,
    });

    useEffect(() => {
        updateContactInformation({ customer: customerValue });
    }, [customerValue, updateContactInformation]);

    useEffect(() => {
        updateContactInformation({ country: countryValue });
    }, [countryValue, updateContactInformation]);

    useEffect(() => {
        updateContactInformation({ differentDeliveryAddress: differentDeliveryAddressValue });
    }, [differentDeliveryAddressValue, updateContactInformation]);

    useEffect(() => {
        updateContactInformation({ deliveryCountry: deliveryCountryValue });
    }, [deliveryCountryValue, updateContactInformation]);

    useEffect(() => {
        updateContactInformation({ newsletterSubscription: newsletterSubscriptionValue });
    }, [updateContactInformation, newsletterSubscriptionValue]);
};