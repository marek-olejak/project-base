import { ProductsSearch } from './ProductsSearch';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SkeletonPageProductsList } from 'components/Blocks/Skeleton/SkeletonPageProductsList';
import { SearchQueryApi, SimpleCategoryFragmentApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type SearchContentProps = {
    searchResults: SearchQueryApi | undefined;
    fetching: boolean;
};

export const SearchContent: FC<SearchContentProps> = ({ searchResults, fetching }) => {
    const router = useRouter();
    const { t } = useTranslation();

    const title = useSeoTitleWithPagination(searchResults?.productsSearch.totalCount, t('Found products'));

    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragmentApi>(searchResults?.categoriesSearch.edges),
        [searchResults?.categoriesSearch.edges],
    );

    const isFetchingInitialData = !searchResults && fetching;

    return (
        <>
            <Heading type="h1">{`${t('Search results for')} "${getStringFromUrlQuery(router.query.q)}"`}</Heading>
            {isFetchingInitialData ? (
                <SkeletonPageProductsList />
            ) : (
                !!searchResults && (
                    <>
                        {!!searchResults.articlesSearch.length && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found articles')}</Heading>
                                <SimpleNavigation
                                    imageType="searchThumbnail"
                                    linkType="article"
                                    listedItems={searchResults.articlesSearch}
                                />
                            </div>
                        )}

                        {!!searchResults.brandSearch.length && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found brands')}</Heading>
                                <SimpleNavigation linkType="brand" listedItems={searchResults.brandSearch} />
                            </div>
                        )}

                        {!!mappedCategoriesSearchResults?.length && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found categories')}</Heading>
                                <SimpleNavigation linkType="category" listedItems={mappedCategoriesSearchResults} />
                            </div>
                        )}

                        <div className="mt-6">
                            <Heading type="h3">{title}</Heading>
                            <ProductsSearch productsSearch={searchResults.productsSearch} />
                        </div>
                    </>
                )
            )}
        </>
    );
};
