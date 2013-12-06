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
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cache\Frontend\StringFrontend;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * @Flow\Scope("singleton")
 */
class RequestCacheService {

	/**
	 * @var StringFrontend
	 */
	protected $cache;

	/**
	 * @Flow\Inject
	 * @var SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @param string $uri
	 * @return bool
	 */
	public function has($uri) {
		$entryIdentifier = $this->createRequestUriCacheRecordIdentifier($uri);
		return $this->cache->has($entryIdentifier);
	}

	/**
	 * @param string $uri
	 * @return bool
	 */
	public function remove($uri) {
		$entryIdentifier = $this->createRequestUriCacheRecordIdentifier($uri);
		return $this->cache->remove($entryIdentifier);
	}

	/**
	 * @param string $uri
	 * @param array $tags
	 * @return void
	 */
	public function set($uri, array $tags = array()) {
		$entryIdentifier = $this->createRequestUriCacheRecordIdentifier($uri);
		$this->cache->set($entryIdentifier, $uri, $tags, 0);
	}

	/**
	 * @param NodeInterface $node
	 */
	public function purgeCacheByNode(NodeInterface $node) {
		foreach ($this->cache->getByTag($node->getIdentifier()) as $uri) {
			$this->purgeCacheByRequestUri($uri);
		}
	}

	/**
	 * Purge Cloudflare cache by URI
	 *
	 * @param string $uri
	 * @todo really purge CF cache
	 */
	public function purgeCacheByRequestUri($uri) {
		$entryIdentifier = $this->createRequestUriCacheRecordIdentifier($uri);
		$this->remove($entryIdentifier);
		$this->systemLogger->log(sprintf("Clear Cloudflare cache for \"%s\"", $uri), LOG_INFO, NULL, 'Cloudflare');
	}

	/**
	 * Create a cache record for the given URI tagged by used Node identifier
	 *
	 * @param string $uri
	 * @param array $nodes
	 */
	public function createRequestUriCacheRecord($uri, array $nodes) {
		$this->removeRequestUriCacheRecord($uri);
		$tags = array();
		foreach ($nodes as $node) {
			/** @var NodeInterface $node */
			$identifier = $node->getIdentifier();
			$tags[$identifier] = $identifier;
		}
		$this->set($uri, $tags);
		$this->systemLogger->log('Create new request cache record', LOG_DEBUG, NULL, 'Cloudflare');
	}

	/**
	 * Remove the cache record for the given URI
	 *
	 * @param string $uri
	 */
	public function removeRequestUriCacheRecord($uri) {
		$entryIdentifier = $this->createRequestUriCacheRecordIdentifier($uri);
		$this->systemLogger->log('Remove existing request cache record', LOG_DEBUG, NULL, 'Cloudflare');
		$this->cache->remove($entryIdentifier);
	}

	/**
	 * Remove the cache record identifier for the given URI
	 *
	 * @param string $uri
	 * @return string
	 */
	public function createRequestUriCacheRecordIdentifier($uri) {
		return md5($uri);
	}
}
