<?php

use Shopsys\Environment;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @{inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new Bmatzner\JQueryBundle\BmatznerJQueryBundle(),
            new Bmatzner\JQueryUIBundle\BmatznerJQueryUIBundle(),
            new Craue\FormFlowBundle\CraueFormFlowBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new FM\ElfinderBundle\FMElfinderBundle(),
            new Fp\JsFormValidatorBundle\FpJsFormValidatorBundle(),
            new Intaro\PostgresSearchBundle\IntaroPostgresSearchBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new Presta\SitemapBundle\PrestaSitemapBundle(),
            new Prezent\Doctrine\TranslatableBundle\PrezentDoctrineTranslatableBundle(),
            new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Shopsys\FormTypesBundle\ShopsysFormTypesBundle(),
            new ShopSys\MigrationBundle\ShopSysMigrationBundle(),
            new Shopsys\ProductFeed\HeurekaBundle\ShopsysProductFeedHeurekaBundle(),
            new Shopsys\ProductFeed\HeurekaDeliveryBundle\ShopsysProductFeedHeurekaDeliveryBundle(),
            new Shopsys\ProductFeed\ZboziBundle\ShopsysProductFeedZboziBundle(),
            new Shopsys\ProductFeed\GoogleBundle\ShopsysProductFeedGoogleBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new VasekPurchart\ConsoleErrorsBundle\ConsoleErrorsBundle(),
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(), // has to be loaded after FrameworkBundle and TwigBundle
            new Shopsys\ShopBundle\ShopsysShopBundle(), // must be loaded as last, because translations must overwrite other bundles
        ];

        if ($this->getEnvironment() === Environment::ENVIRONMENT_DEVELOPMENT) {
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
        }

        if ($this->getEnvironment() === Environment::ENVIRONMENT_TEST) {
            $bundles[] = new Shopsys\IntegrationTestingBundle\ShopsysIntegrationTestingBundle();
        }

        return $bundles;
    }

    /**
     * @{inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        foreach ($this->getConfigs() as $filename) {
            if (file_exists($filename) && is_readable($filename)) {
                $loader->load($filename);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getConfigs()
    {
        $configs = [
            __DIR__ . '/config/parameters_common.yml',
            __DIR__ . '/config/parameters.yml',
            __DIR__ . '/config/paths.yml',
            __DIR__ . '/config/config.yml',
            __DIR__ . '/config/security.yml',
        ];
        switch ($this->getEnvironment()) {
            case Environment::ENVIRONMENT_DEVELOPMENT:
                $configs[] = __DIR__ . '/config/config_dev.yml';
                break;
            case Environment::ENVIRONMENT_TEST:
                $configs[] = __DIR__ . '/config/parameters_test.yml';
                $configs[] = __DIR__ . '/config/config_test.yml';
                break;
        }

        return $configs;
    }

    /**
     * @{inheritdoc}
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * @{inheritdoc}
     */
    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    /**
     * @{inheritdoc}
     */
    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }
}
