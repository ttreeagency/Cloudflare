<?php
namespace Ttree\Cloudflare\Factory;

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
use TYPO3\Flow\Utility\Arrays;

/**
 * CloudFlare cache definition factory
 *
 * @Flow\Scope("singleton")
 */
class CacheDefinitionFactory {

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
	 * Create a Cloudflare Cache Definition object
	 *
	 * @param string $zone
	 * @param string $apiKey
	 * @param string $email
	 * @return CacheDefinition
	 * @throws \Ttree\Cloudflare\Exception
	 */
	public function create($zone = NULL, $apiKey = NULL, $email = NULL) {
		$zone = $zone ?: $this->getZoneFromSettings();
		if (!is_string($zone) || trim($zone) === '') {
			throw new \Ttree\Cloudflare\Exception('Zone must not be empty', 1386348250);
		}
		$apiKey = $apiKey ?: $this->getApiKeyFromSettings($zone);
		$email = $email ?: $this->getEmailFromSettings($zone);

		return new CacheDefinition($zone, $apiKey, $email);
	}

	/**
	 * Get zone from default settings
	 */
	protected function getZoneFromSettings() {
		return Arrays::getValueByPath($this->settings, 'default.zone');
	}

	/**
	 * Get API key from settings
	 *
	 * @param $zone
	 * @return string
	 */
	protected function getApiKeyFromSettings($zone) {
		return Arrays::getValueByPath($this->settings, str_replace('.', '_', $zone) . '.apiKey') ?: Arrays::getValueByPath($this->settings, 'default.apiKey');
	}

	/**
	 * Get email address from settings
	 *
	 * @param $zone
	 * @return string
	 */
	protected function getEmailFromSettings($zone) {
		return Arrays::getValueByPath($this->settings, str_replace('.', '_', $zone) . '.email') ?: Arrays::getValueByPath($this->settings, 'default.email');
	}
}