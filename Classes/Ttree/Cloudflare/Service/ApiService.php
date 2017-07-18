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

use Ttree\Cloudflare\Browser;
use Ttree\Cloudflare\CacheDefinition;
use Ttree\Cloudflare\Client;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\SystemLoggerInterface;

/**
 * @Flow\Scope("singleton")
 */
class ApiService
{
    /**
     * @Flow\Inject
     * @var Client
     */
    protected $client;

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Core\Bootstrap
     */
    protected $bootstrap;

    /**
     * Retrieve domain statistics for a given time frame
     *
     * @param integer $interval
     * @param CacheDefinition $cacheDefinition
     * @return array
     */
    public function getStatistics($interval, CacheDefinition $cacheDefinition)
    {
        return $this->client->request($cacheDefinition, 'stats', array(
            'z' => $cacheDefinition->getZone(),
            'interval' => (integer)$interval
        ));
    }

    /**
     * Retrieve the list of domains
     *
     * @param CacheDefinition $cacheDefinition
     * @return array
     */
    public function getAllDomains(CacheDefinition $cacheDefinition)
    {
        return $this->client->request($cacheDefinition, 'zone_load_multi');
    }

    /**
     * Retrieve DNS Records of a given domain
     *
     * @param CacheDefinition $cacheDefinition
     * @return array
     */
    public function getAllDnsRecordsByDomainDefinition(CacheDefinition $cacheDefinition)
    {
        return $this->client->request($cacheDefinition, 'rec_load_all', array(
            'z' => $cacheDefinition->getZone()
        ));
    }

    /**
     * Enable Development Mode
     *
     * @param CacheDefinition $cacheDefinition
     * @return array
     */
    public function enableDevelopmentMode(CacheDefinition $cacheDefinition)
    {
        return $this->client->request($cacheDefinition, 'devmode', array(
            'z' => $cacheDefinition->getZone(),
            'v' => 1
        ));
    }

    /**
     * Disable Development Mode
     *
     * @param CacheDefinition $cacheDefinition
     * @return array
     */
    public function disableDevelopmentMode(CacheDefinition $cacheDefinition)
    {
        return $this->client->request($cacheDefinition, 'devmode', array(
            'z' => $cacheDefinition->getZone(),
            'v' => 0
        ));
    }

    /**
     * Purge All Cache
     *
     * @param CacheDefinition $cacheDefinition
     * @return array
     */
    public function purgeAllCache(CacheDefinition $cacheDefinition)
    {
        return $this->client->request($cacheDefinition, 'fpurge_ts', array(
            'z' => $cacheDefinition->getZone(),
            'v' => 1
        ));
    }

    /**
     * Purge a single file
     *
     * @param string $uri
     * @param CacheDefinition $cacheDefinition
     * @return array
     */
    public function purgeCacheByUri($uri, CacheDefinition $cacheDefinition)
    {
        $result = $this->client->request($cacheDefinition, 'zone_file_purge', array(
            'z' => $cacheDefinition->getZone(),
            'url' => $uri
        ));

        $this->systemLogger->log(sprintf('Purge Cloudflare cache for "%s"', $uri), LOG_INFO, null, 'Cloudflare');

        return $result;
    }
}
