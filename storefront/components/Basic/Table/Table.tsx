import { ReactNode } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type TableProps = {
    head?: ReactNode;
};

export const Table: FC<TableProps> = ({ head, children, className }) => (
    <div className={twMergeCustom('overflow-x-auto rounded border-2 border-skyBlue p-6', className)}>
        <table className="w-full">
            {!!head && <thead className="border-b border-skyBlue">{head}</thead>}
            <tbody>{children}</tbody>
        </table>
    </div>
);

type CellProps = {
    align?: 'center' | 'right';
    isWithoutWrap?: boolean;
    isHead?: boolean;
    colSpan?: number;
};

export const Row: FC = ({ children, className }) => (
    <tr className={twMergeCustom('border-b border-skyBlue text-graySlate last:border-b-0', className)}>{children}</tr>
);

export const Cell: FC<CellProps> = ({ align, isHead, isWithoutWrap, children, className, colSpan }) => {
    const Tag = isHead ? 'th' : 'td';

    return (
        <Tag
            colSpan={colSpan}
            className={twMergeCustom(
                'px-2 py-4 text-sm text-dark ',

                align === 'center' && 'text-center',
                align === 'right' && 'text-right',

                isWithoutWrap && 'whitespace-nowrap',
                className,
            )}
        >
            {children}
        </Tag>
    );
};

export const CellHead: FC<CellProps> = ({ className, children, ...props }) => (
    <Cell isHead className={twMergeCustom('font-bold text-graySlate', className)} {...props}>
        {children}
    </Cell>
);

export const CellMinor: FC<CellProps> = ({ className, children, ...props }) => (
    <Cell className={twMergeCustom(' text-graySlate', className)} {...props}>
        {children}
    </Cell>
);
