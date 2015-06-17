<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class SourceFilterTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class SourceFilterTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @return array
	 */
	public function filterParamsProvider() {
		return array(
				array('source' => 'test case', 'expectedResult' => '&source=test%20case'),
				array('source' => '', 'expectedResult' => ''),
				array('source' => null, 'expectedResult' => '')
		);
	}

	/**
	 * @params string $source
	 * @params string $expectedResult
	 * @test
	 * @dataProvider filterParamsProvider
	 */
	public function canSetFilterParams($source, $expectedResult) {
		$instance = new \Searchperience\Api\Client\Domain\Filters\SourceFilter;
		$instance->setSource($source);

		$this->assertEquals($expectedResult, $instance->getFilterString());
	}
}