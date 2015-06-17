<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/25/14
 * @Time: 3:59 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class FiltersCollectionTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class FilterCollectionTestCase extends \Searchperience\Tests\BaseTestCase {
	/**
	 * @var \Searchperience\Api\Client\Domain\Filters\FilterCollection
	 */
	protected $instance;

	/**
	 * Initialize test environment
	 *
	 * @return void
	 */
	public function setUp() {
		$this->instance = new \Searchperience\Api\Client\Domain\Filters\FilterCollection;
	}

	/**
	 * Cleanup test environment
	 *
	 * @return void
	 */
	public function tearDown() {
		$this->instance = null;
	}

	/**
	 * @test
	 */
	public function canAddFilter() {
		$this->assertEquals(0, $this->instance->getCount(),'Unexpected initial filterCount');
	}


}