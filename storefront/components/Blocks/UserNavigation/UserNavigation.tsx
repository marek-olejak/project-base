import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { Button } from 'components/Forms/Button/Button';
import { m } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';
import { useLogout } from 'utils/auth/useLogout';
import { useUserMenuItems } from 'utils/user/useUserMenuItems';

const getMenuItemTwClass = (menuItemLink?: string, asPath?: string) =>
    twJoin(
        'border-b border-borderAccent last:border-none no-underline px-2 py-3 flex justify-between gap-4',
        menuItemLink && asPath?.includes(menuItemLink)
            ? 'bg-backgroundAccentLess text-textAccent hover:text-textAccent'
            : 'bg-backgroundMore text-text hover:text-textAccent',
    );

export const UserNavigation: FC = () => {
    const userMenuItems = useUserMenuItems();
    const router = useRouter();
    const { t } = useTranslation();
    const [isExpanded, setIsExpanded] = useState(false);
    const logoutHandler = useLogout();

    return (
        <div className="flex flex-col min-w-[300px] rounded-md overflow-hidden h-fit">
            <button
                className={twJoin(
                    'items-center last:border-none bg-backgroundMore no-underline px-6 py-3 flex justify-between gap-4 lg:hidden',
                    isExpanded && 'border-b border-borderAccent',
                )}
                onClick={() => setIsExpanded((prev) => !prev)}
            >
                {isExpanded ? t('Hide menu') : t('Show menu')}
                <ArrowIcon className={twJoin('h-3 w-3 transition', isExpanded ? 'rotate-180' : 'rotate-0')} />
            </button>

            <m.div
                key="user-navigation"
                animate={isExpanded ? 'open' : 'closed'}
                className="!flex flex-col lg:!h-auto px-4 bg-backgroundMore"
                initial={false}
                variants={collapseExpandAnimation}
            >
                {userMenuItems.map((menuItem, index) => (
                    <ExtendedNextLink
                        key={index}
                        className={getMenuItemTwClass(menuItem.link, router.asPath)}
                        href={menuItem.link}
                    >
                        {menuItem.text}
                        {menuItem.count && (
                            <div
                                className={twJoin(
                                    'min-w-[24px] px-1 items-center justify-center min-h-[24px] flex text-sm rounded-full bg-backgroundDark text-textInverted',
                                )}
                            >
                                {menuItem.count}
                            </div>
                        )}
                    </ExtendedNextLink>
                ))}
                <Button
                    className={twJoin(
                        getMenuItemTwClass(),
                        'font-normal normal-case bg-backgroundMore hover:bg-backgroundMore text-text hover:text-textAccent hover:underline',
                    )}
                    onClick={logoutHandler}
                >
                    {t('Logout')}
                </Button>
            </m.div>
        </div>
    );
};
