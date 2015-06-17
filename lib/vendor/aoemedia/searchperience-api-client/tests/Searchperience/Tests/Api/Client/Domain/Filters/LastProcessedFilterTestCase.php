<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class LastProcessedFilterTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class LastProcessedFilterTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @return array
	 */
	public function filterParamsProvider() {
		return array(
			array(
				'processStart' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
				'processEnd' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
				'expectedResult' => '&processStart=2014-01-01%2010%3A00%3A00&processEnd=2014-01-01%2010%3A00%3A00'
			),
			array(
				'processStart' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
				'processEnd' => null,
				'expectedResult' => '&processStart=2014-01-01%2010%3A00%3A00'
			),
			array(
				'processStart' => null,
				'processEnd' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
				'expectedResult' => '&processEnd=2014-01-01%2010%3A00%3A00'
			),
			array(
				'processStart' => null,
				'processEnd' => null,
				'expectedResult' => ''
			),
		);
	}

	/**
	 * @params string $processStart
	 * @params string $processEnd
	 * @params string $expectedResult
	 * @test
	 * @dataProvider filterParamsProvider
	 */
	public function canSetFilterParams($processStart, $processEnd, $expectedResult) {
		$instance = new \Searchperience\Api\Client\Domain\Filters\LastProcessedFilter();
		$instance->injectDateTimeService(new \Searchperience\Api\Client\System\DateTime\DateTimeService());

		if ($processStart instanceof \DateTime) {
			$instance->setProcessStart($processStart);
		}

		if ($processEnd instanceof \DateTime) {
			$instance->setProcessEnd($processEnd);

		}

		$this->assertEquals($expectedResult, $instance->getFilterString());
	}
}
