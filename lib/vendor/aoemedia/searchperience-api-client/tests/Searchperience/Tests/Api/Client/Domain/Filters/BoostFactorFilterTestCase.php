<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class BoostFactorFilterTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class BoostFactorFilterTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @return array
	 */
	public function filterParamsProvider() {
		return array(
				array('boostFactorStart' => 0.001, 'boostFactorEnd' => 123.00, 'expectedResult' => '&boostFactorStart=0.001&boostFactorEnd=123'),
				array('boostFactorStart' => 0.001, 'boostFactorEnd' => null, 'expectedResult' => '&boostFactorStart=0.001'),
				array('boostFactorStart' => null, 'boostFactorEnd' => 158.569, 'expectedResult' => '&boostFactorEnd=158.569'),
				array('boostFactorStart' => null, 'boostFactorEnd' => null, 'expectedResult' => ''),
		);
	}
	/**
	 * @params string $boostFactorStart
	 * @params string $boostFactorEnd
	 * @params string $expectedResult
	 * @test
	 * @dataProvider filterParamsProvider
	 */
	public function canSetFilterParams($boostFactorStart, $boostFactorEnd, $expectedResult) {
		$instance = new \Searchperience\Api\Client\Domain\Filters\BoostFactorFilter;
		$instance->setBoostFactorStart($boostFactorStart);
		$instance->setBoostFactorEnd($boostFactorEnd);

		$this->assertEquals($expectedResult, $instance->getFilterString());
	}
}
