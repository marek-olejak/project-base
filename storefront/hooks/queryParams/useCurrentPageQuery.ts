import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'helpers/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { useRouter } from 'next/router';
import { UrlQueries } from 'types/urlQueries';

export const useCurrentPageQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentPageQuery = Number(query[PAGE_QUERY_PARAMETER_NAME] || 1);

    return currentPageQuery;
};
