import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { formatPrice } from 'utils/formaters/formatPrice';

type FormatPriceFunctionType = (price: string | number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const { t } = useTranslation();
    const [{ data: settingsData }] = useSettingsQuery();
    const { defaultLocale = 'en' } = useDomainConfig();

    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = settingsData?.settings?.pricing ?? {};
    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : parseFloat(price));

    return (price, options) =>
        formatPrice(getPriceAsFloat(price), defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
