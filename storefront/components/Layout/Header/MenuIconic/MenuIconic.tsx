import { MenuIconicItem, MenuIconicItemLink } from './MenuIconicElements';
import { CompareIcon, HeartIcon, MarkerIcon } from 'components/Basic/Icon/IconsSvg';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useComparison } from 'hooks/comparison/useComparison';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useWishlist } from 'hooks/useWishlist';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';

const TEST_IDENTIFIER = 'layout-header-menuiconic';

const MenuIconicItemUserAuthenticated = dynamic(() =>
    import('components/Layout/Header/MenuIconic/MenuIconicItemUserAuthenticated').then(
        (component) => component.MenuIconicItemUserAuthenticated,
    ),
);

const MenuIconicItemUserUnauthenticated = dynamic(() =>
    import('components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticated').then(
        (component) => component.MenuIconicItemUserUnauthenticated,
    ),
);

export const MenuIconic: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [storesUrl, productComparisonUrl, wishlistUrl] = getInternationalizedStaticUrls(
        ['/stores', '/product-comparison', '/wishlist'],
        url,
    );
    const { comparison } = useComparison();
    const { wishlist } = useWishlist();
    const isUserLoggedIn = useIsUserLoggedIn();

    return (
        <ul className="flex items-center gap-1" data-testid={TEST_IDENTIFIER}>
            <MenuIconicItem className="max-lg:hidden" dataTestId={TEST_IDENTIFIER + '-stores'}>
                <MenuIconicItemLink href={storesUrl}>
                    <MarkerIcon className="w-4 text-white" />
                    {t('Stores')}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem className="relative" dataTestId={TEST_IDENTIFIER + '-login'}>
                {isUserLoggedIn ? (
                    <MenuIconicItemUserAuthenticated className="relative" dataTestId={TEST_IDENTIFIER + '-login'} />
                ) : (
                    <MenuIconicItemUserUnauthenticated dataTestId={TEST_IDENTIFIER + '-login'} />
                )}
            </MenuIconicItem>

            <MenuIconicItem className="max-lg:hidden" dataTestId={TEST_IDENTIFIER + '-comparison'}>
                <MenuIconicItemLink href={productComparisonUrl} title={t('Comparison')}>
                    <CompareIcon className="w-4 text-white" />
                    {!!comparison?.products.length && <span>{comparison.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>

            <MenuIconicItem className="max-lg:hidden" dataTestId={TEST_IDENTIFIER + '-wishlist'}>
                <MenuIconicItemLink href={wishlistUrl} title={t('Wishlist')}>
                    <HeartIcon className="w-4 text-white" isFull={!!wishlist?.products.length} />
                    {!!wishlist?.products.length && <span>{wishlist.products.length}</span>}
                </MenuIconicItemLink>
            </MenuIconicItem>
        </ul>
    );
};
