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

use Ttree\Cloudflare\CacheDefinition;
use Ttree\Cloudflare\Service\CacheService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * CloudFlare cache command controller
 */
class CacheCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var CacheService
	 */
	protected $cacheService;

	/**
	 * @var array
	 */
	protected $settings;

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
	 * @param string $zone
	 * @param integer $interval
	 */
	public function statsCommand($zone, $interval = 30) {
		$cacheDefinition = new CacheDefinition($this->settings['apiKey'], $this->settings['email'], $zone);
		$this->output("\n");
		try {
			$statistics = $this->cacheService->getStatistics($interval, $cacheDefinition);

			$this->output("Page Views:\t\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.trafficBreakdown.pageviews.regular')));
			$this->output("Page Views (Threat):\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.trafficBreakdown.pageviews.threat')));
			$this->output("Page Views (Crawler):\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.trafficBreakdown.pageviews.crawler')));
			$this->output("\n");
			$this->output("Bandwidth Served (CF):\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.bandwidthServed.cloudflare')));
			$this->output("\n");
			$this->output("Pro Account:\t\t\t%s\n", array(Arrays::getValueByPath($statistics, 'response.result.objs.0.pro_zone') ? 'Yes' : 'No'));
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->output("Unable to get statistics ...\n");
		}
	}

	/**
	 * @param string $zone
	 */
	public function purgeAllCacheCommand($zone) {
		$cacheDefinition = new CacheDefinition($this->settings['apiKey'], $this->settings['email'], $zone);
		$this->output("\n");
		try {
			$this->cacheService->purgeAllCache($cacheDefinition);
			$this->output("Purging cache in progress ...\n");
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->output("Unable to purge cache ...\n");
		}
	}

	/**
	 * @param string $zone
	 * @param string $url
	 */
	public function purgeCacheCommand($zone, $url) {
		$cacheDefinition = new CacheDefinition($this->settings['apiKey'], $this->settings['email'], $zone);
		$this->output("\n");
		try {
			$this->cacheService->purgeCacheByUrl($url, $cacheDefinition);

			$this->output("Purging cache for \"%s\" in progress ...\n", array($url));
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->output("Unable to purge cache for \"%s\" ...\n", array($url));
		}
	}
}

?>