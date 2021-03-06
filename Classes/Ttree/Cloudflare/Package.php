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

use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Ttree Cloudflare Package
 */
class Package extends BasePackage
{
    /**
     * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Workspace', 'afterNodePublishing', 'Ttree\Cloudflare\Service\RequestCacheService', 'purgeCacheByNode');
    }
}
