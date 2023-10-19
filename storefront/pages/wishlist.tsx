import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import SharedWishlist from 'components/Pages/Wishlist/SharedWishlist';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

const WishlistPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    useGtmPageViewEvent(useGtmStaticPageViewEvent(GtmPageType.other));
    const currentDomainConfig = useDomainConfig();

    const [wishlistUrl] = getInternationalizedStaticUrls(['/wishlist'], currentDomainConfig.url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Wishlist'), slug: wishlistUrl }];
    const router = useRouter();
    const urlQueryParamId = router.query.id as string | undefined;

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('Wishlist')}>
            <Webline>{urlQueryParamId ? <SharedWishlist urlQueryParamId={urlQueryParamId} /> : <Wishlist />}</Webline>
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default WishlistPage;
