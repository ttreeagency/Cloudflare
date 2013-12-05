<?php
namespace Ttree\Cloudflare\Service;

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
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CacheService {

	CONST CLOUDFLARE_API_URI = 'https://www.cloudflare.com/api_json.html';

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\CurlEngine
	 */
	protected $browserRequestEngine;

	/**
	 * Initialize object
	 */
	public function initializeObject() {
		$this->browser->setRequestEngine($this->browserRequestEngine);
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 * @return array
	 * @throws \Ttree\Cloudflare\Exception
	 */
	protected function request($method = 'GET', array $arguments = array()) {
		$response = $this->browser->request(self::CLOUDFLARE_API_URI, $method, $arguments);

		if ($response->getStatusCode() !== 200) {
			throw new \Ttree\Cloudflare\Exception(
				sprintf('HTTP request do not return 200 status code (%s)', $response->getStatusCode()),
				1386263549
			);
		}

		$response = json_decode($response->getContent(), TRUE);
		if ($response['result'] !== 'success') {
			throw new \Ttree\Cloudflare\Exception(
				sprintf('JSON payload do not contain a success status (%s)', $response['result']),
				1386263553
			);
		}

		return $response;
	}

	/**
	 * Retrieve domain statistics for a given time frame
	 *
	 * @param integer $interval
	 * @param CacheDefinition $cacheDefinition
	 * @return array
	 */
	public function getStatistics($interval, CacheDefinition $cacheDefinition) {
		$result = $this->request('POST', array(
			'a' => 'stats',
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail(),
			'z' => $cacheDefinition->getZone(),
			'interval' => (integer)$interval
		));

		return $result;
	}

	/**
	 * Retrieve the list of domains
	 *
	 * @param CacheDefinition $cacheDefinition
	 * @return array
	 */
	public function getAllDomains(CacheDefinition $cacheDefinition) {
		$result = $this->request('POST', array(
			'a' => 'zone_load_multi',
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail()
		));

		return $result;
	}

	/**
	 * Retrieve DNS Records of a given domain
	 *
	 * @param CacheDefinition $cacheDefinition
	 * @return array
	 */
	public function getAllDnsRecordsByDomainDefinition(CacheDefinition $cacheDefinition) {
		$result = $this->request('POST', array(
			'a' => 'rec_load_all',
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail(),
			'z' => $cacheDefinition->getZone()
		));

		return $result;
	}

	/**
	 * Enable Development Mode
	 *
	 * @param CacheDefinition $cacheDefinition
	 * @return array
	 */
	public function enableDevelopmentMode(CacheDefinition $cacheDefinition) {
		$result = $this->request('POST', array(
			'a' => 'devmode',
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail(),
			'z' => $cacheDefinition->getZone(),
			'v' => 1
		));

		return $result;
	}

	/**
	 * Disable Development Mode
	 *
	 * @param CacheDefinition $cacheDefinition
	 * @return array
	 */
	public function disableDevelopmentMode(CacheDefinition $cacheDefinition) {
		$result = $this->request('POST', array(
			'a' => 'devmode',
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail(),
			'z' => $cacheDefinition->getZone(),
			'v' => 0
		));

		return $result;
	}

	/**
	 * Purge All Cache
	 *
	 * @param CacheDefinition $cacheDefinition
	 * @return array
	 */
	public function purgeAllCache(CacheDefinition $cacheDefinition) {
		$result = $this->request('POST', array(
			'a' => 'fpurge_ts',
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail(),
			'z' => $cacheDefinition->getZone(),
			'v' => 1
		));

		return $result;
	}

	/**
	 * Purge a single file
	 *
	 * @param string $url
	 * @param CacheDefinition $cacheDefinition
	 * @return array
	 */
	public function purgeCacheByUrl($url, CacheDefinition $cacheDefinition) {
		$result = $this->request('POST', array(
			'a' => 'zone_file_purge',
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail(),
			'z' => $cacheDefinition->getZone(),
			'url' => $url
		));

		return $result;
	}
}
