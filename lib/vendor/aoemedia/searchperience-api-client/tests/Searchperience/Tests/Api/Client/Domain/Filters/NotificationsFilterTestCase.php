<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

/**
 * Class NotificationsFilterTestCase
 * @package Searchperience\Api\Client\Domain\Filters
 */
class NotificationsFilterTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @return array
	 */
	public function filterParamsProvider() {
		return array(
				array('isduplicateof' => true, 'lasterror' => true, 'processingthreadid' => true, 'expectedResult' => '&isduplicateof=1&lasterror=1&processingthreadid=1'),
				array('isduplicateof' => false, 'lasterror' => true, 'processingthreadid' => true, 'expectedResult' => '&lasterror=1&processingthreadid=1'),
				array('isduplicateof' => false, 'lasterror' => false, 'processingthreadid' => true, 'expectedResult' => '&processingthreadid=1'),
				array('isduplicateof' => false, 'lasterror' => false, 'processingthreadid' => false, 'expectedResult' => '')
		);
	}

	/**
	 * @params string $isduplicateof
	 * @params string $lasterror
	 * @params string $processingthreadid
	 * @params string $expectedResult
	 * @test
	 * @dataProvider filterParamsProvider
	 */
	public function canSetFilterParams($isduplicateof, $lasterror, $processingthreadid, $expectedResult) {
		$instance = new \Searchperience\Api\Client\Domain\Filters\NotificationsFilter;
		$instance->setIsduplicateof($isduplicateof);
		$instance->setLasterror($lasterror);
		$instance->setProcessingthreadid($processingthreadid);

		$this->assertEquals($expectedResult, $instance->getFilterString());
	}
}