<?php
namespace Ttree\Cloudflare\Tests\Unit\Factory;

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
use TYPO3\Flow\Annotations as Flow;

/**
 * Test for CacheDefinitionFactory
 */
class CacheDefinitionFactoryTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function factoryReturnAllDefaultValueByDefault() {
		$cacheDefinitionFactory = new CacheDefinitionFactory();
		$cacheDefinitionFactory->injectSettings(array(
			'default' => array(
				'zone' => 'domain.com',
				'apiKey' => '1234',
				'email' => 'info@domain.com'
			)
		));

		$cacheDefinition = $cacheDefinitionFactory->create();

		$this->assertSame('domain.com', $cacheDefinition->getZone());
		$this->assertSame('1234', $cacheDefinition->getApiKey());
		$this->assertSame('info@domain.com', $cacheDefinition->getEmail());
	}

	/**
	 * @test
	 * @expectedException \Ttree\Cloudflare\Exception
	 */
	public function factoryReturnAnExceptionIfTheZoneIfEmpty() {
		$cacheDefinitionFactory = new CacheDefinitionFactory();
		$cacheDefinitionFactory->injectSettings(array(
			'default' => array(
				'zone' => '',
				'apiKey' => '1234',
				'email' => 'info@domain.com'
			)
		));

		$cacheDefinitionFactory->create();
	}

	/**
	 * @test
	 */
	public function factoryReturnGetDefaultValueFromSettings() {
		$cacheDefinitionFactory = new CacheDefinitionFactory();
		$cacheDefinitionFactory->injectSettings(array(
			'default' => array(
				'zone' => 'domain.com',
				'apiKey' => '1234',
				'email' => 'info@domain.com'
			),
			'lost_com' => array(
				'zone' => 'lost.com',
				'apiKey' => '5678',
				'email' => 'info@lost.com'
			)
		));

		$cacheDefinitionFactory->create();

		$cacheDefinition = $cacheDefinitionFactory->create('domain.com');

		$this->assertSame('domain.com', $cacheDefinition->getZone());
		$this->assertSame('1234', $cacheDefinition->getApiKey());
		$this->assertSame('info@domain.com', $cacheDefinition->getEmail());

		$cacheDefinition = $cacheDefinitionFactory->create('lost.com');

		$this->assertSame('lost.com', $cacheDefinition->getZone());
		$this->assertSame('5678', $cacheDefinition->getApiKey());
		$this->assertSame('info@lost.com', $cacheDefinition->getEmail());
	}

	/**
	 * @test
	 */
	public function factoryCanCreateDefintionNotFoundInSettings() {
		$cacheDefinitionFactory = new CacheDefinitionFactory();
		$cacheDefinitionFactory->injectSettings(array(
			'default' => array(
				'zone' => 'domain.com',
				'apiKey' => '1234',
				'email' => 'info@domain.com'
			)
		));

		$cacheDefinition = $cacheDefinitionFactory->create('google.com', 'info@google.com', 'ABC');

		$this->assertSame('google.com', $cacheDefinition->getZone());
		$this->assertSame('info@google.com', $cacheDefinition->getApiKey());
		$this->assertSame('ABC', $cacheDefinition->getEmail());
	}

}

?>