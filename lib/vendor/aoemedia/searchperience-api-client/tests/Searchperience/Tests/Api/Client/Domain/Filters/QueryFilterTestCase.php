<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class QueryFilterTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class QueryFilterTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @return array
	 */
	public function filterParamsProvider() {
		return array(
				array('query' => 'test case', 'queryFields' => 'id,url', 'expectedResult' => '&query=test%20case&queryFields=id%2Curl'),
				array('query' => 'test case', 'queryFields' => null, 'expectedResult' => '&query=test%20case'),
				array('query' => null, 'queryFields' => 'id,url', 'expectedResult' => '&queryFields=id%2Curl'),
				array('query' => null, 'queryFields' => null, 'expectedResult' => ''),
				array('query' => '', 'queryFields' => '', 'expectedResult' => ''),
		);
	}

	/**
	 * @params string $query
	 * @params string $queryFields
	 * @params string $expectedResult
	 * @test
	 * @dataProvider filterParamsProvider
	 */
	public function canSetFilterParams($query, $queryFields, $expectedResult) {
		$instance = new \Searchperience\Api\Client\Domain\Filters\QueryFilter;
		$instance->setQueryString($query);
		$instance->setQueryFields($queryFields);

		$this->assertEquals($expectedResult, $instance->getFilterString());
	}
}