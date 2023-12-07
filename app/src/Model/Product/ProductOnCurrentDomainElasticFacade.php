<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade as BaseProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;

/**
 * @method \App\Model\Product\Product getVisibleProductById(int $productId)
 * @method \App\Model\Product\Product[] getAccessoriesForProduct(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getVariantsForProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsForSearch(string $searchText, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataForSearch(string|null $searchText, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @property \App\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataForAll(\App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsInCategory(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataInCategory(int $categoryId, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $searchText = "")
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @property \App\Model\Product\Brand\BrandRepository $brandRepository
 */
class ProductOnCurrentDomainElasticFacade extends BaseProductOnCurrentDomainElasticFacade
{
    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \App\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        ProductAccessoryRepository $productAccessoryRepository,
        ProductElasticsearchRepository $productElasticsearchRepository,
        ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository,
        FilterQueryFactory $filterQueryFactory,
    ) {
        parent::__construct(
            $productRepository,
            $domain,
            $currentCustomerUser,
            $productAccessoryRepository,
            $productElasticsearchRepository,
            $productFilterCountDataElasticsearchRepository,
            $filterQueryFactory,
        );
    }

    /**
     * @param int $flagId
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataForFlag(
        int $flagId,
        ProductFilterData $productFilterData,
        string $searchText = '',
    ): ProductFilterCountData {
        $filterQuery = $this->filterQueryFactory->createListableProductsByFlagIdWithPriceAndStockFilter(
            $flagId,
            $productFilterData,
        );

        if ($searchText !== '') {
            $filterQuery = $filterQuery->search($searchText);
        }

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $filterQuery,
        );
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return int[]
     */
    public function getCategoryIdsForFilterData(ProductFilterData $productFilterData)
    {
        return $this->productElasticsearchRepository->getCategoryIdsForFilterData($productFilterData);
    }

    /**
     * @param int $brandId
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataForBrand(
        int $brandId,
        ProductFilterData $productFilterData,
        string $searchText = '',
    ): ProductFilterCountData {
        $filterQuery = $this->filterQueryFactory->createListableProductsByBrandIdWithPriceAndStockFilter(
            $brandId,
            $productFilterData,
        );

        if ($searchText !== '') {
            $filterQuery = $filterQuery->search($searchText);
        }

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $filterQuery,
        );
    }
}
