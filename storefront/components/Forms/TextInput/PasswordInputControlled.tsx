import { TextInput } from './TextInput';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { InputHTMLAttributes, ReactElement, useCallback, useState } from 'react';
import { Control, useController } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, never, 'name'>;

type PasswordInputProps = NativeProps & {
    label: string;
    inputSize?: 'small' | 'default';
    dataTestId?: string;
};

type PasswordInputControlledProps = {
    name: string;
    render: (input: JSX.Element) => ReactElement<any, any> | null;
    passwordInputProps: PasswordInputProps;
    control: Control<any>;
    formName: string;
};

export const PasswordInputControlled: FC<PasswordInputControlledProps> = ({
    name,
    render,
    control,
    passwordInputProps,
    formName,
}) => {
    const {
        fieldState: { invalid, error },
        field,
    } = useController({ name, control });
    const passwordInputId = formName + name;

    const [inputType, setInputType] = useState<'text' | 'password'>('password');

    const togglePasswordVisibilityHandler = useCallback(() => {
        setInputType((currentInputType) => (currentInputType === 'password' ? 'text' : 'password'));
    }, []);

    return render(
        <>
            <TextInput
                required
                dataTestId={passwordInputProps.dataTestId}
                hasError={invalid}
                id={passwordInputId}
                inputSize={passwordInputProps.inputSize}
                label={passwordInputProps.label}
                name={name}
                ref={field.ref}
                type={inputType}
                value={field.value}
                onBlur={field.onBlur}
                onChange={field.onChange}
            >
                <img
                    alt="eye icon"
                    src="/svg/eye.svg"
                    className={twJoin(
                        'absolute top-1/2 right-4 w-6 -translate-y-1/2 cursor-pointer',
                        inputType === 'text' && 'opacity-50',
                    )}
                    onClick={togglePasswordVisibilityHandler}
                />
            </TextInput>
            <FormLineError error={error} inputType="text-input-password" textInputSize={passwordInputProps.inputSize} />
        </>,
    );
};
