<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PageRankFilter
 * @package Searchperience\Api\Client\Domain\Filters
 */
class PageRankFilter extends AbstractFilter {

	/**
	 * @var string
	 */
	protected $filterString;

	/**
	 * @var string $pageRankStart
	 * @Assert\Type(type="double", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $pageRankStart;

	/**
	 * @var string $pageRankEnd
	 * @Assert\Type(type="double", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $pageRankEnd;

	/**
	 * @param string $pageRankEnd
	 */
	public function setPageRankEnd($pageRankEnd) {
		$this->pageRankEnd = $pageRankEnd;
	}

	/**
	 * @return string
	 */
	public function getPageRankEnd() {
		return $this->pageRankEnd;
	}

	/**
	 * @param string $pageRankStart
	 */
	public function setPageRankStart($pageRankStart) {
		$this->pageRankStart = $pageRankStart;
	}

	/**
	 * @return string
	 */
	public function getPageRankStart() {
		return $this->pageRankStart;
	}

	/**
	 * @return string
	 */
	public function getFilterString() {
		if (!empty($this->pageRankStart)) {
			$this->filterString = sprintf("&pageRankStart=%s", rawurlencode($this->getPageRankStart()));
		}
		if (!empty($this->pageRankEnd)) {
			$this->filterString .= sprintf("&pageRankEnd=%s", rawurlencode($this->getPageRankEnd()));
		}
		return $this->filterString;
	}
}