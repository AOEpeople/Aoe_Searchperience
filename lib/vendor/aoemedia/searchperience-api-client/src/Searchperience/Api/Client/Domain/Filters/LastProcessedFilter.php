<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */
namespace Searchperience\Api\Client\Domain\Filters;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LastProcessedFilter
 * @package Searchperience\Api\Client\Domain\Filters
 */
class LastProcessedFilter extends AbstractDateFilter {

	/**
	 * @var string
	 */
	protected $filterString;

	/**
	 * @var \DateTime $processStart
	 * @Assert\DateTime(message="The value {{ value }} is not a valid datetime.")
	 */
	protected $processStart;

	/**
	 * @var \DateTime $processEnd
	 * @Assert\DateTime(message="The value {{ value }} is not a valid datetime.")
	 */
	protected $processEnd;

	/**
	 * @param \DateTime $processEnd
	 */
	public function setProcessEnd($processEnd) {
		$this->processEnd = $processEnd;
	}

	/**
	 * @return \DateTime
	 */
	public function getProcessEnd() {
		return $this->processEnd;
	}

	/**
	 * @param \DateTime $processStart
	 */
	public function setProcessStart($processStart) {
		$this->processStart = $processStart;
	}

	/**
	 * @return \DateTime
	 */
	public function getProcessStart() {
		return $this->processStart;
	}

	/**
	 * @return string
	 */
	public function getFilterString() {
		if (!empty($this->processStart)) {
			$this->filterString = sprintf("&processStart=%s", rawurlencode($this->toString($this->getProcessStart())));
		}
		if (!empty($this->processEnd)) {
			$this->filterString .= sprintf("&processEnd=%s", rawurlencode($this->toString($this->getProcessEnd())));
		}
		return $this->filterString;
	}
}