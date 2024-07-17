import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { forwardRef, InputHTMLAttributes, MouseEventHandler, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'id',
    'disabled' | 'name' | 'onBlur' | 'checked' | 'onChange'
>;

export type RadiobuttonProps = NativeProps & {
    value: any;
    checked: InputHTMLAttributes<HTMLInputElement>['checked'];
    label: ReactNode;
    onClick?: (newValue: string | null) => void;
    labelWrapperClassName?: string;
};

export const Radiobutton = forwardRef<HTMLInputElement, RadiobuttonProps>(
    (
        { label, onChange, id, name, checked, value, disabled, onBlur, onClick, labelWrapperClassName },
        radiobuttonForwardedRef,
    ) => {
        const onClickHandler: MouseEventHandler<HTMLInputElement> = (event) => {
            if (!onClick) {
                return;
            }

            if (checked) {
                onClick(null);
            } else {
                onClick(event.currentTarget.value);
            }
        };

        return (
            <LabelWrapper
                checked={checked}
                className={labelWrapperClassName}
                disabled={disabled}
                htmlFor={id}
                inputType="radio"
                label={label}
            >
                <input
                    checked={checked}
                    className="peer sr-only"
                    disabled={disabled}
                    id={id}
                    name={name}
                    readOnly={!onChange}
                    ref={radiobuttonForwardedRef}
                    type="radio"
                    value={value}
                    onBlur={onBlur}
                    onChange={onChange}
                    onClick={onClickHandler}
                />
            </LabelWrapper>
        );
    },
);

Radiobutton.displayName = 'Radiobutton';
