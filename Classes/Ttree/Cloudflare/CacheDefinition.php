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

/**
 * Cloudflare Cache Definition
 */
class CacheDefinition
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $zone;

    /**
     * @var boolean
     */
    protected $enable;

    /**
     * @param string $apiKey
     * @param string $email
     * @param string $zone
     * @param boolean $enable
     */
    public function __construct($zone, $apiKey, $email, $enable)
    {
        $this->zone = $zone;
        $this->apiKey = $apiKey;
        $this->email = $email;
        $this->enable = (boolean)$enable;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }
}
