<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository as BaseProductFilterCountDataElasticsearchRepository;

/**
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @method __construct(\Elasticsearch\Client $client, \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer $aggregationResultToCountDataTransformer)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataInSearch(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $baseFilterQuery)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataInCategory(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $baseFilterQuery)
 * @method int[] calculateFlagsPlusNumbers(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $plusFlagsQuery)
 * @method int[] calculateBrandsPlusNumbers(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $plusBrandsQuery)
 * @method replaceParametersPlusNumbers(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData $countData, \App\Model\Product\Search\FilterQuery $plusParametersQuery)
 * @method array calculateParameterPlusNumbers(\App\Model\Product\Filter\ParameterFilterData $parameterFilterData, \App\Model\Product\Search\FilterQuery $parameterFilterQuery)
 */
class ProductFilterCountDataElasticsearchRepository extends BaseProductFilterCountDataElasticsearchRepository
{
}
