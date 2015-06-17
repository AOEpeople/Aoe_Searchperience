<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */
namespace Searchperience\Api\Client\Domain\Filters;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NotificationsFilter
 * @package Searchperience\Api\Client\Domain\Filters
 */
class NotificationsFilter extends AbstractFilter {

	/**
	 * @var string
	 */
	protected $filterString;

	/**
	 * @var string $isduplicateof
	 * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $isduplicateof;

	/**
	 * @var string $lasterror
	 * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $lasterror;

	/**
	 * @var string $processingthreadid
	 * @Assert\Type(type="bool", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $processingthreadid;

	/**
	 * @param string $isduplicateof
	 */
	public function setIsduplicateof($isduplicateof) {
		$this->isduplicateof = $isduplicateof;
	}

	/**
	 * @return string
	 */
	public function getIsduplicateof() {
		return $this->isduplicateof;
	}

	/**
	 * @param string $lasterror
	 */
	public function setLasterror($lasterror) {
		$this->lasterror = $lasterror;
	}

	/**
	 * @return string
	 */
	public function getLasterror() {
		return $this->lasterror;
	}

	/**
	 * @param string $processingthreadid
	 */
	public function setProcessingthreadid($processingthreadid) {
		$this->processingthreadid = $processingthreadid;
	}

	/**
	 * @return string
	 */
	public function getProcessingthreadid() {
		return $this->processingthreadid;
	}

	/**
	 * @return string
	 */
	public function getFilterString() {
		if (!empty($this->isduplicateof)) {
			$this->filterString = sprintf("&isduplicateof=%d", rawurlencode($this->getIsduplicateof()));
		}
		if (!empty($this->lasterror)) {
			$this->filterString .= sprintf("&lasterror=%d", rawurlencode($this->getLasterror()));
		}
		if (!empty($this->processingthreadid)) {
			$this->filterString .= sprintf("&processingthreadid=%d", rawurlencode($this->getProcessingthreadid()));
		}
		return $this->filterString;
	}
}