import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { Translate } from 'next-translate';
import { FieldValues, Path, UseFormReturn } from 'react-hook-form';
import { CombinedError } from 'urql';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export const handleFormErrors = <T extends FieldValues>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
    t: Translate,
    errorMessage: string | undefined = undefined,
    fields: Record<string, { name: string }> | undefined = undefined,
    origin: GtmMessageOriginType = GtmMessageOriginType.other,
): void => {
    if (error === undefined) {
        return;
    }

    const { userError, applicationError } = getUserFriendlyErrors(error, t);

    if (applicationError !== undefined) {
        showErrorMessage(errorMessage !== undefined ? errorMessage : applicationError.message, origin);
    }

    if (userError?.validation !== undefined) {
        for (const fieldName in userError.validation) {
            if (fields !== undefined && Object.keys(fields).some((fieldKey) => fields[fieldKey].name === fieldName)) {
                formProviderMethods.setError(fieldName as Path<T>, userError.validation[fieldName]);
                continue;
            }
            showErrorMessage(userError.validation[fieldName].message, origin);
        }
    }
};
