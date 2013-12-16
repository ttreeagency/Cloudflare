<?php
namespace Ttree\Cloudflare\TypoScript;

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
use Ttree\Cloudflare\Service\RequestCacheService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

/**
 * Tag the current request with all nodes used to render the current page
 */
class CacheImplementation extends AbstractTypoScriptObject {

	/**
	 * @Flow\Inject
	 * @var SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Inject
	 * @var RequestCacheService
	 */
	protected $requestCacheService;

	/**
	 * @Flow\Inject
	 * @var CacheDefinitionFactory
	 */
	protected $cacheDefinitionFactory;

	/**
	 * @var NodeInterface
	 */
	protected $documentNode;

	/**
	 * @var string
	 */
	protected $zone;

	/**
	 * @var boolean
	 */
	protected $enable;

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $documentNode
	 */
	public function setDocumentNode($documentNode) {
		$this->documentNode = $documentNode;
	}

	/**
	 * @param string $zone
	 */
	public function setZone($zone) {
		$this->zone = $zone;
	}

	/**
	 * @return boolean
	 */
	public function getEnable() {
		return $this->enable;
	}

	/**
	 * Tag the current request URI with all used node identifier
	 *
	 * The node identifier tag are used later to purge the cache when publishing node. This method work only in
	 * live workspace (first display of the current request)
	 *
	 * @return void
	 */
	public function evaluate() {
		try {
			/** @var ActionRequest $request */
			$request = $this->tsRuntime->getControllerContext()->getRequest()->getMainRequest();
			$cacheDefinition = $this->cacheDefinitionFactory->create($this->tsValue('zone'));
			if ($cacheDefinition->getEnable() === FALSE || $this->tsValue('enable') === FALSE) {
				$this->systemLogger->log('Cloudflare Request cache disabled', LOG_DEBUG, NULL, 'Cloudflare');
				return;
			}

			/** @var NodeInterface $documentNode */
			$documentNode = $this->tsValue('documentNode');
			if ($documentNode->getContext()->getWorkspace(FALSE)->getName() !== 'live') {
				return;
			}

			$uriBuilder = clone $this->tsRuntime->getControllerContext()->getUriBuilder();
			$uriBuilder->setRequest($request);

			$uri = $uriBuilder
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->setFormat($request->getFormat())
				->uriFor('show', array('node' => $documentNode->getIdentifier()), 'Frontend\Node', 'TYPO3.Neos');

			if ($this->requestCacheService->has($uri)) {
				$this->systemLogger->log(sprintf('Request cache record exist for "%s"', $uri), LOG_DEBUG, NULL, 'Cloudflare');
				return;
			}

			$this->requestCacheService->createRequestUriCacheRecord($uri, $cacheDefinition);
		} catch (\Ttree\Cloudflare\Exception $exception) {
			$this->systemLogger->logException($exception);
		}
	}
}