import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { ContactInformationAddress } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationAddress';
import { ContactInformationCompany } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationCompany';
import { ContactInformationCustomer } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationCustomer';
import { ContactInformationDeliveryAddress } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationDeliveryAddress';
import { ContactInformationUser } from 'components/Pages/Order/ContactInformation/FormBlocks/ContactInformationUser';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';

export const ContactInformationFormContent: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const customerValue = useWatch({ name: formMeta.fields.customer.name, control: formProviderMethods.control });
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });

    return (
        <div className="overflow-hidden transition-all" ref={cssTransitionRef}>
            <div ref={contentElement}>
                <div className="mb-10">
                    <ContactInformationCustomer />
                </div>

                <div className="mb-10">
                    <ContactInformationUser />
                </div>

                {customerValue === 'companyCustomer' && (
                    <div className="mb-10">
                        <ContactInformationCompany />
                    </div>
                )}

                <div className="mb-10">
                    <ContactInformationAddress />
                </div>

                <ContactInformationDeliveryAddress />

                <div className="h4 mb-3">{t('Note')}</div>
                <TextareaControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.note.name}
                    render={(textarea) => (
                        <FormLine bottomGap className="flex-none lg:w-[65%]">
                            {textarea}
                        </FormLine>
                    )}
                    textareaProps={{
                        label: formMeta.fields.note.label,
                        rows: 3,
                        onChange: (event) => updateContactInformation({ note: event.currentTarget.value }),
                    }}
                />

                {settingsData?.settings?.heurekaEnabled && (
                    <CheckboxControlled
                        control={formProviderMethods.control}
                        formName={formMeta.formName}
                        name={formMeta.fields.isWithoutHeurekaAgreement.name}
                        render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                        checkboxProps={{
                            label: formMeta.fields.isWithoutHeurekaAgreement.label,
                        }}
                    />
                )}
            </div>
        </div>
    );
};
