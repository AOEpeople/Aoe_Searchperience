<?php
/**
 * @Author: Nikolay Diaur <timo.schmidt@aoe.com>
 * @Date: 03/04/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

use Guzzle\Plugin\Backoff\AbstractBackoffStrategy;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractDateFilter
 * @package Searchperience\Api\Client\Domain\Filters
 */
abstract class AbstractDateFilter extends AbstractFilter{

	/**
	 * @var \Searchperience\Api\Client\System\DateTime\DateTimeService
	 */
	protected $dateTimeService;

	/**
	 * @param \Searchperience\Api\Client\System\DateTime\DateTimeService $dateTimeService
	 * @return void
	 */
	public function injectDateTimeService(\Searchperience\Api\Client\System\DateTime\DateTimeService $dateTimeService) {
		$this->dateTimeService = $dateTimeService;
	}

	/**
	 * @param mixed $date
	 * @return string
	 */
	protected function toString($date) {
		if(!$date instanceof \DateTime) {
			return '';
		}
		return $this->dateTimeService->getDateStringFromDateTime($date);
	}
}