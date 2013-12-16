<?php
namespace Ttree\Cloudflare;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Ttree.Cloudflare".      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Client\CurlEngine;

/**
 * Cloudflare API Browser
 */
class Client {

	CONST CLOUDFLARE_API_URI = 'https://www.cloudflare.com/api_json.html';

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var CurlEngine
	 */
	protected $browserRequestEngine;

	/**
	 * Initialize object
	 */
	public function initializeObject() {
		$this->browser->setRequestEngine($this->browserRequestEngine);
	}

	/**
	 * Send request to CloudFlare API
	 *
	 * @param CacheDefinition $cacheDefinition
	 * @param string $action
	 * @param array $arguments
	 * @param string $method
	 * @return array
	 * @throws \Ttree\Cloudflare\Exception
	 */
	public function request(CacheDefinition $cacheDefinition, $action, array $arguments = array(), $method = 'POST') {
		$arguments = array_merge(array(
			'a' => $action,
			'tkn' => $cacheDefinition->getApiKey(),
			'email' => $cacheDefinition->getEmail()
		), $arguments);

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

}