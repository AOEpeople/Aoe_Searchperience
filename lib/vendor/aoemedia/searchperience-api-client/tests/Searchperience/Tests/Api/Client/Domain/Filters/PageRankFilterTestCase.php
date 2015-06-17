<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class PageRankFilterTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class PageRankFilterTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @return array
	 */
	public function filterParamsProvider() {
		return array(
				array('pageRankStart' => 0.001, 'pageRankEnd' => 123.00, 'expectedResult' => '&pageRankStart=0.001&pageRankEnd=123'),
				array('pageRankStart' => 0.001, 'pageRankEnd' => null, 'expectedResult' => '&pageRankStart=0.001'),
				array('pageRankStart' => null, 'pageRankEnd' => 158.569, 'expectedResult' => '&pageRankEnd=158.569'),
				array('pageRankStart' => null, 'pageRankEnd' => null, 'expectedResult' => ''),
		);
	}

	/**
	 * @params string $pageRankStart
	 * @params string $pageRankEnd
	 * @params string $expectedResult
	 * @test
	 * @dataProvider filterParamsProvider
	 */
	public function canSetFilterParams($pageRankStart, $pageRankEnd, $expectedResult) {
		$instance = new \Searchperience\Api\Client\Domain\Filters\PageRankFilter;
		$instance->setPageRankStart($pageRankStart);
		$instance->setPageRankEnd($pageRankEnd);

		$this->assertEquals($expectedResult, $instance->getFilterString());
	}
}