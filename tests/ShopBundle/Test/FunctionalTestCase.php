<?php

namespace Tests\ShopBundle\Test;

use Shopsys\Environment;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    protected function setUpDomain()
    {
        $domain = $this->getContainer()->get('shopsys.shop.component.domain');
        /* @var $domain \Shopsys\ShopBundle\Component\Domain\Domain */
        $domain->switchDomainById(1);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->setUpDomain();
    }

    /**
     * @param bool $createNew
     * @param string $username
     * @param string $password
     * @param array $kernelOptions
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function getClient(
        $createNew = false,
        $username = null,
        $password = null,
        $kernelOptions = []
    ) {
        $defaultKernelOptions = [
            'environment' => Environment::ENVIRONMENT_TEST,
            'debug' => Environment::isEnvironmentDebug(Environment::ENVIRONMENT_TEST),
        ];

        $kernelOptions = array_replace($defaultKernelOptions, $kernelOptions);

        if ($createNew) {
            $this->client = $this->createClient($kernelOptions);
            $this->setUpDomain();
        } elseif (!isset($this->client)) {
            $this->client = $this->createClient($kernelOptions);
        }

        if ($username !== null) {
            $this->client->setServerParameters([
                'PHP_AUTH_USER' => $username,
                'PHP_AUTH_PW' => $password,
            ]);
        }

        return $this->client;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getClient()->getContainer();
    }

    /**
     * @param string $referenceName
     * @return object
     */
    protected function getReference($referenceName)
    {
        $persistentReferenceFacade = $this->getContainer()
            ->get('shopsys.shop.component.data_fixture.persistent_reference_facade');
        /* @var $persistentReferenceFacade \Shopsys\ShopBundle\Component\DataFixture\PersistentReferenceFacade */

        return $persistentReferenceFacade->getReference($referenceName);
    }

    /**
     * @param string $className
     * @return object
     */
    protected function getServiceByType($className)
    {
        $serviceByTypeLocator = $this->getContainer()->get('shopsys_integration_testing.service_by_type_locator');

        return $serviceByTypeLocator->getByType($className);
    }
}
