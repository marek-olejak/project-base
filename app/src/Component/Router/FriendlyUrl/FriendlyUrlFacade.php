<?php

declare(strict_types=1);

namespace App\Component\Router\FriendlyUrl;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade as BaseFriendlyUrlFacade;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;

/**
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFactory $friendlyUrlFactory
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory $friendlyUrlUniqueResultFactory, \App\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\Router\FriendlyUrl\FriendlyUrlFactory $friendlyUrlFactory, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider $friendlyUrlCacheKeyProvider, \Symfony\Contracts\Cache\CacheInterface $mainFriendlyUrlSlugCache)
 * @method resolveUniquenessOfFriendlyUrl(\App\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl, string $entityName)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl[] getAllByRouteNameAndEntityId(string $routeName, int $entityId)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl|null findMainFriendlyUrl(int $domainId, string $routeName, int $entityId)
 * @method setFriendlyUrlAsMain(\App\Component\Router\FriendlyUrl\FriendlyUrl $mainFriendlyUrl)
 * @method string getAbsoluteUrlByFriendlyUrl(\App\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl)
 * @method renewMainFriendlyUrlSlugCache(\App\Component\Router\FriendlyUrl\FriendlyUrl $mainFriendlyUrl)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl getMainFriendlyUrl(int $domainId, string $routeName, int $entityId)
 * @method \App\Component\Router\FriendlyUrl\FriendlyUrl|null findByDomainIdAndSlug(int $domainId, string $slug)
 * @method array<int,\App\Component\Router\FriendlyUrl\FriendlyUrl|null> getMainFriendlyUrlsIndexedByDomains(string $routeName, int $entityId)
 */
class FriendlyUrlFacade extends BaseFriendlyUrlFacade
{
    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchFormData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNonUsedFriendlyUrlQueryBuilderByDomainIdAndQuickSearch(
        int $domainId,
        QuickSearchFormData $quickSearchFormData,
    ): QueryBuilder {
        return $this->friendlyUrlRepository->getNonUsedFriendlyUrlQueryBuilderByDomainIdAndQuickSearch(
            $domainId,
            $quickSearchFormData,
        );
    }

    /**
     * @param int $domainId
     * @param string $slug
     */
    public function removeFriendlyUrl(int $domainId, string $slug): void
    {
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainId, $slug);

        if ($friendlyUrl === null) {
            return;
        }

        $this->em->remove($friendlyUrl);
        $this->em->flush();
    }

    /**
     * @param int $domainId
     * @param string $slug
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlData $friendlyUrlData
     */
    public function setRedirect(int $domainId, string $slug, FriendlyUrlData $friendlyUrlData): void
    {
        /** @var \App\Component\Router\FriendlyUrl\FriendlyUrl|null $friendlyUrl */
        $friendlyUrl = $this->friendlyUrlRepository->findByDomainIdAndSlug($domainId, $slug);

        if ($friendlyUrl === null) {
            return;
        }

        $friendlyUrl->setRedirectCode($friendlyUrlData->redirectCode);
        $friendlyUrl->setRedirectTo($friendlyUrlData->redirectTo);
        $friendlyUrl->setLastModification(new DateTime('now', new DateTimeZone('UTC')));
        $this->em->flush();
    }

    /**
     * @return array<int, string>
     */
    public function getUndefinedRouteNamesInMapping(): array
    {
        $routeNameMapping = $this->friendlyUrlRepository->getRouteNameToEntityMap();
        $allUsedRouteNames = $this->friendlyUrlRepository->getAllRouteNames();

        $undefinedRouteNameMappings = [];

        foreach ($allUsedRouteNames as $usedRouteName) {
            if (!array_key_exists($usedRouteName, $routeNameMapping)) {
                $undefinedRouteNameMappings[] = $usedRouteName;
            }
        }

        return $undefinedRouteNameMappings;
    }

    /**
     * @param string $routeName
     * @return string
     */
    public function getEntityClassByRouteName(string $routeName): string
    {
        $routeNameMapping = $this->friendlyUrlRepository->getRouteNameToEntityMap();

        if (array_key_exists($routeName, $routeNameMapping)) {
            return $routeNameMapping[$routeName];
        }

        throw new FriendlyUrlNotFoundException();
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string
     */
    public function getAbsoluteUrlByRouteNameAndEntityId(int $domainId, string $routeName, int $entityId): string
    {
        $mainFriendlyUrlSlug = $this->getMainFriendlyUrlSlug($domainId, $routeName, $entityId);
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        if ($mainFriendlyUrlSlug === SeoPage::SEO_PAGE_HOMEPAGE_SLUG) {
            return $domainConfig->getUrl();
        }

        return $domainConfig->getUrl() . '/' . $mainFriendlyUrlSlug;
    }
}
