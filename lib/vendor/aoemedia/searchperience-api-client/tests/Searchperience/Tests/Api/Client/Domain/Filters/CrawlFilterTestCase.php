<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class CrawlFilterTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class CrawlFilterTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @return array
	 */
	public function filterParamsProvider() {
		return array(
				array(
					'crawlStart' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
					'crawlEnd' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
					'expectedResult' => '&crawlStart=2014-01-01%2010%3A00%3A00&crawlEnd=2014-01-01%2010%3A00%3A00'
				),
				array(
					'crawlStart' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
					'crawlEnd' => null,
					'expectedResult' => '&crawlStart=2014-01-01%2010%3A00%3A00'
				),
				array(
					'crawlStart' => null,
					'crawlEnd' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
					'expectedResult' => '&crawlEnd=2014-01-01%2010%3A00%3A00'
				),
				array(
					'crawlStart' => null,
					'crawlEnd' => null,
					'expectedResult' => ''
				),
		);
	}

	/**
	 * @params string $crawlStart
	 * @params string $crawlEnd
	 * @params string $expectedResult
	 * @test
	 * @dataProvider filterParamsProvider
	 */
	public function canSetFilterParams($crawlStart, $crawlEnd, $expectedResult) {
		$instance = new \Searchperience\Api\Client\Domain\Filters\CrawlFilter;
		$instance->injectDateTimeService(new \Searchperience\Api\Client\System\DateTime\DateTimeService());

		if($crawlStart instanceof \DateTime) {
			$instance->setCrawlStart($crawlStart);
		}

		if($crawlEnd instanceof \DateTime) {
			$instance->setCrawlEnd($crawlEnd);
		}

		$this->assertEquals($expectedResult, $instance->getFilterString());
	}
}
