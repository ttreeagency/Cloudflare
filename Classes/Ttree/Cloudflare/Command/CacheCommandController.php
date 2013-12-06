<?php
namespace Ttree\Cloudflare\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Cloudflare".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Ttree\Cloudflare\Factory\CacheDefinitionFactory;
use Ttree\Cloudflare\Service\ApiService;
use Ttree\Cloudflare\Service\RequestCacheService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * CloudFlare cache command controller
 */
class CacheCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var ApiService
	 */
	protected $apiService;

	/**
	 * @Flow\Inject
	 * @var RequestCacheService
	 */
	protected $requestCacheService;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @Flow\Inject
	 * @var CacheDefinitionFactory
	 */
	protected $cacheDefinitionFactory;

	/**
	 * Inject the settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Display basic statistics
	 *
	 * @param string $zone
	 * @param integer $interval
	 */
	public function statsCommand($zone, $interval = 30) {
		$cacheDefinition = $this->cacheDefinitionFactory->create($zone);
		$this->output("\n");
		try {
			$statistics = $this->apiService->getStatistics($interval, $cacheDefinition);

			$this->output("Page Views:\t\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.trafficBreakdown.pageviews.regular')));
			$this->output("Page Views (Threat):\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.trafficBreakdown.pageviews.threat')));
			$this->output("Page Views (Crawler):\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.trafficBreakdown.pageviews.crawler')));
			$this->output("\n");
			$this->output("Bandwidth Served (CF):\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.bandwidthServed.cloudflare')));
			$this->output("Bandwidth Served (Client):\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.bandwidthServed.user')));
			$this->output("\n");
			$this->output("Pro Account:\t\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.pro_zone') ? 'Yes' : 'No'));
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->output("Unable to get statistics ...\n");
		}
	}

	/**
	 * Purge all cache
	 *
	 * @param string $zone
	 */
	public function purgeAllCacheCommand($zone) {
		$cacheDefinition = $this->cacheDefinitionFactory->create($zone);
		$this->output("\n");
		try {
			$this->apiService->purgeAllCache($cacheDefinition);
			$this->output("Purging cache in progress ...\n");
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->output("Unable to purge cache ...\n");
		}
	}

	/**
	 * Purge cache for the given URI
	 *
	 * @param string $zone
	 * @param string $uri
	 */
	public function purgeCacheCommand($zone, $uri) {
		$cacheDefinition = $this->cacheDefinitionFactory->create($zone);
		$this->output("\n");
		try {
			$this->apiService->purgeCacheByUri($uri, $cacheDefinition);
			$this->requestCacheService->remove($uri);

			$this->output("Purging cache for \"%s\" in progress ...\n", array($uri));
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->output("Unable to purge cache for \"%s\" ...\n", array($uri));
		}
	}

	/**
	 * Enable or disable the development mode
	 *
	 * @param string $zone
	 * @param boolean $disable
	 */
	public function developmentModeCommand($zone, $disable = FALSE) {
		$cacheDefinition = $this->cacheDefinitionFactory->create($zone);
		$this->output("\n");
		try {
			if ($disable === FALSE) {
				$this->apiService->enableDevelopmentMode($cacheDefinition);
				$this->output("Development mode enabled ...\n");
			} else {
				$this->apiService->disableDevelopmentMode($cacheDefinition);
				$this->output("Development mode disabled ...\n");
			}
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->output("Unable to configurer development mode ...\n");
		}
	}
}