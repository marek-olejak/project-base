<?php

namespace Shopsys\ShopBundle\Component\Router\FriendlyUrl;

use Shopsys\ShopBundle\Component\Domain\Config\DomainConfig;
use Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator;
use Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class FriendlyUrlRouter implements RouterInterface {

	/**
	 * @var \Symfony\Component\Routing\RequestContext
	 */
	private $context;

	/**
	 * @var \Symfony\Component\Config\Loader\DelegatingLoader
	 */
	private $delegatingLoader;

	/**
	 * @var \Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator
	 */
	private $friendlyUrlGenerator;

	/**
	 * @var \Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher
	 */
	private $friendlyUrlMatcher;

	/**
	 * @var \Shopsys\ShopBundle\Component\Domain\Config\DomainConfig
	 */
	private $domainConfig;

	/**
	 * @var string
	 */
	private $friendlyUrlRouterResourceFilepath;

	/**
	 * @var \Symfony\Component\Routing\RouteCollection
	 */
	private $collection;

	/**
	 * @param \Symfony\Component\Routing\RequestContext $context
	 * @param \Symfony\Component\Config\Loader\DelegatingLoader $delegatingLoader
	 * @param \Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator $friendlyUrlGenerator
	 * @param \Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrlMatcher $friendlyUrlMatcher
	 * @param \Shopsys\ShopBundle\Component\Domain\Config\DomainConfig $domainConfig
	 * @param string $friendlyUrlRouterResourceFilepath
	 */
	public function __construct(
		RequestContext $context,
		DelegatingLoader $delegatingLoader,
		FriendlyUrlGenerator $friendlyUrlGenerator,
		FriendlyUrlMatcher $friendlyUrlMatcher,
		DomainConfig $domainConfig,
		$friendlyUrlRouterResourceFilepath
	) {
		$this->context = $context;
		$this->delegatingLoader = $delegatingLoader;
		$this->friendlyUrlGenerator = $friendlyUrlGenerator;
		$this->friendlyUrlMatcher = $friendlyUrlMatcher;
		$this->domainConfig = $domainConfig;
		$this->friendlyUrlRouterResourceFilepath = $friendlyUrlRouterResourceFilepath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContext(RequestContext $context) {
		$this->context = $context;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRouteCollection() {
		if ($this->collection === null) {
			$this->collection = $this->delegatingLoader->load($this->friendlyUrlRouterResourceFilepath);
		}

		return $this->collection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function generate($routeName, $parameters = [], $referenceType = self::ABSOLUTE_PATH) {
		return $this->friendlyUrlGenerator->generateFromRouteCollection(
			$this->getRouteCollection(),
			$this->domainConfig,
			$routeName,
			$parameters,
			$referenceType
		);
	}

	/**
	 * @param \Shopsys\ShopBundle\Component\Router\FriendlyUrl\FriendlyUrl $friendlyUrl
	 * @param array $parameters
	 * @param string $referenceType
	 * @return string
	 */
	public function generateByFriendlyUrl(FriendlyUrl $friendlyUrl, $parameters = [], $referenceType = self::ABSOLUTE_PATH) {
		return $this->friendlyUrlGenerator->getGeneratedUrl(
			$friendlyUrl->getRouteName(),
			$this->getRouteCollection()->get($friendlyUrl->getRouteName()),
			$friendlyUrl,
			$parameters,
			$referenceType
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function match($pathinfo) {
		return $this->friendlyUrlMatcher->match($pathinfo, $this->getRouteCollection(), $this->domainConfig);
	}

}