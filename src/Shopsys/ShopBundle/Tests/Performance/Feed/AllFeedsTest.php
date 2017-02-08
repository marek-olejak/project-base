<?php

namespace Shopsys\ShopBundle\Tests\Performance\Feed;

use Doctrine\ORM\EntityManager;
use Shopsys\ShopBundle\Component\Domain\Config\DomainConfig;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Router\CurrentDomainRouter;
use Shopsys\ShopBundle\Model\Feed\FeedConfig;
use Shopsys\ShopBundle\Model\Feed\FeedConfigFacade;
use Shopsys\ShopBundle\Tests\Performance\Feed\PerformanceResultsCsvExporter;
use Shopsys\ShopBundle\Tests\Performance\Feed\PerformanceTestSample;
use Shopsys\ShopBundle\Tests\Test\FunctionalTestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Routing\RouterInterface;

class AllFeedsTest extends FunctionalTestCase {

	const MAX_DURATION_FEED_SECONDS = 180;
	const MAX_DURATION_DELIVERY_FEED_SECONDS = 20;
	const SUSPICIOUSLY_LOW_DURATION_SECONDS = 5;

	const ROUTE_NAME_GENERATE_FEED = 'admin_feed_generate';
	const ADMIN_USERNAME = 'admin';
	const ADMIN_PASSWORD = 'admin123';

	public function testAllFeedsGeneration() {
		$performanceResultsCsvExporter = $this->getContainer()->get(PerformanceResultsCsvExporter::class);
		/* @var $performanceResultsCsvExporter \Shopsys\ShopBundle\Tests\Performance\Feed\PerformanceResultsCsvExporter */
		$consoleOutput = new ConsoleOutput();

		$consoleOutput->writeln('');
		$consoleOutput->writeln('<fg=cyan>Testing generation of all feeds:</fg=cyan>');

		$performanceTestSamples = [];
		$allFeedGenerationData = $this->getAllFeedGenerationData();
		foreach ($allFeedGenerationData as $feedGenerationData) {
			list($feedConfig, $domainConfig, $maxDuration) = $feedGenerationData;
			/* @var $feedConfig \Shopsys\ShopBundle\Model\Feed\FeedConfig */
			/* @var $domainConfig \Shopsys\ShopBundle\Component\Domain\Config\DomainConfig */

			$consoleOutput->writeln(
				sprintf(
					'Generating feed "%s" (%s) for %s (domain ID %d)...',
					$feedConfig->getLabel(),
					$feedConfig->getFeedName(),
					$domainConfig->getName(),
					$domainConfig->getId()
				)
			);

			$performanceTestSample = $this->doTestFeedGeneration($feedConfig, $domainConfig, $maxDuration);
			$consoleOutput->writeln($performanceTestSample->getMessage());

			$performanceTestSamples[] = $performanceTestSample;
		}

		$performanceResultsCsvExporter->exportJmeterCsvReport(
			$performanceTestSamples,
			$this->getContainer()->getParameter('shopsys.root_dir') . '/build/stats/performance-tests-feeds.csv'
		);

		$this->assertSamplesAreSuccessful($performanceTestSamples);
	}

	/**
	 * @param \Shopsys\ShopBundle\Model\Feed\FeedConfig $feedConfig
	 * @param \Shopsys\ShopBundle\Component\Domain\Config\DomainConfig $domainConfig
	 * @param int $maxDuration
	 * @return \Shopsys\ShopBundle\Tests\Performance\Feed\PerformanceTestSample
	 */
	private function doTestFeedGeneration(FeedConfig $feedConfig, DomainConfig $domainConfig, $maxDuration) {
		$performanceTestSample = $this->generateFeed($feedConfig, $domainConfig);
		$this->setPerformanceTestSampleMessage($performanceTestSample, $maxDuration, $performanceTestSample->getDuration());

		return $performanceTestSample;
	}

	/**
	 * @return array[]
	 */
	public function getAllFeedGenerationData() {
		$feedConfigFacade = $this->getContainer()->get(FeedConfigFacade::class);
		/* @var $feedConfigFacade \Shopsys\ShopBundle\Model\Feed\FeedConfigFacade */
		$domain = $this->getContainer()->get(Domain::class);
		/* @var $domain \Shopsys\ShopBundle\Component\Domain\Domain */

		$feedGenerationData = $this->getFeedGenerationData(
			$feedConfigFacade->getFeedConfigs(),
			$domain->getAll(),
			self::MAX_DURATION_FEED_SECONDS
		);
		$deliveryFeedGenerationData = $this->getFeedGenerationData(
			$feedConfigFacade->getDeliveryFeedConfigs(),
			$domain->getAll(),
			self::MAX_DURATION_DELIVERY_FEED_SECONDS
		);

		return array_merge($feedGenerationData, $deliveryFeedGenerationData);
	}

	/**
	 * @param \Shopsys\ShopBundle\Model\Feed\FeedConfig[] $feedConfigs
	 * @param \Shopsys\ShopBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
	 * @param int $maxDuration
	 * @return array[]
	 */
	private function getFeedGenerationData(array $feedConfigs, array $domainConfigs, $maxDuration) {
		$feedGenerationData = [];
		foreach ($domainConfigs as $domainConfig) {
			foreach ($feedConfigs as $feedConfig) {
				$feedGenerationData[] = [$feedConfig, $domainConfig, $maxDuration];
			}
		}

		return $feedGenerationData;
	}

	/**
	 * @param \Shopsys\ShopBundle\Tests\Performance\Feed\PerformanceTestSample $performanceTestSample
	 * @param int $maxDuration
	 * @param float $realDuration
	 */
	private function setPerformanceTestSampleMessage(PerformanceTestSample $performanceTestSample, $maxDuration, $realDuration) {
		$minDuration = self::SUSPICIOUSLY_LOW_DURATION_SECONDS;

		if ($realDuration < $minDuration) {
			$message = sprintf('<fg=yellow>Feed generated in %.2F s, which is suspiciously fast.</fg=yellow>', $realDuration);
			$failMessage = sprintf('Feed was generated faster than in %d s, which is suspicious and should be checked.', $minDuration);
			$performanceTestSample->addFailMessage($failMessage);
		} elseif ($realDuration <= $maxDuration) {
			$message = sprintf('<fg=green>Feed generated in %.2F s.</fg=green>', $realDuration);
		} else {
			$message = sprintf('<fg=red>Feed generated in %.2F s, exceeding limit of %d s.</fg=red>', $realDuration, $maxDuration);
			$failMessage = sprintf('Feed generation exceeded limit of %d s.', $maxDuration);
			$performanceTestSample->addFailMessage($failMessage);
		}

		$performanceTestSample->setMessage($message);
	}

	/**
	 * @param \Shopsys\ShopBundle\Model\Feed\FeedConfig $feedConfig
	 * @param \Shopsys\ShopBundle\Component\Domain\Config\DomainConfig $domainConfig
	 * @return \Shopsys\ShopBundle\Tests\Performance\Feed\PerformanceTestSample
	 */
	private function generateFeed(FeedConfig $feedConfig, DomainConfig $domainConfig) {
		$client = $this->getClient(true, self::ADMIN_USERNAME, self::ADMIN_PASSWORD);

		$router = $this->getContainer()->get(CurrentDomainRouter::class);
		/* @var $router \Shopsys\ShopBundle\Component\Router\CurrentDomainRouter */
		$clientEntityManager = $client->getContainer()->get(EntityManager::class);
		/* @var $clientEntityManager \Doctrine\ORM\EntityManager */

		$feedGenerationParameters = [
			'feedName' => $feedConfig->getFeedName(),
			'domainId' => $domainConfig->getId(),
		];
		$uri = $router->generate(self::ROUTE_NAME_GENERATE_FEED, $feedGenerationParameters, RouterInterface::RELATIVE_PATH);

		$clientEntityManager->beginTransaction();

		$startTime = microtime(true);
		$client->request('GET', $uri);
		$endTime = microtime(true);

		$clientEntityManager->rollback();

		$duration = $endTime - $startTime;
		$statusCode = $client->getResponse()->getStatusCode();

		$performanceTestSample = new PerformanceTestSample($feedConfig, $domainConfig, $uri, $duration, $statusCode);

		$expectedStatusCode = 302;
		if ($statusCode !== $expectedStatusCode) {
			$failMessage = sprintf('Admin request on %s ended with status code %d, expected %d.', $uri, $statusCode, $expectedStatusCode);
			$performanceTestSample->addFailMessage($failMessage);
		}

		return $performanceTestSample;
	}

	/**
	 * @param \Shopsys\ShopBundle\Tests\Performance\Feed\PerformanceTestSample[] $performanceTestSamples
	 */
	private function assertSamplesAreSuccessful(array $performanceTestSamples) {
		$failMessages = [];

		foreach ($performanceTestSamples as $performanceTestSample) {
			if (!$performanceTestSample->isSuccessful()) {
				$failMessages[] = sprintf(
					'Generation of feed "%s" on domain with ID %d failed: %s',
					$performanceTestSample->getFeedConfig()->getFeedName(),
					$performanceTestSample->getDomainConfig()->getId(),
					implode(' ', $performanceTestSample->getFailMessages())
				);
			}
			$this->addToAssertionCount(1);
		}

		if (count($failMessages) > 0) {
			$this->fail(implode("\n", $failMessages));
		}
	}

}
