<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class QueryFilter
 * @package Searchperience\Api\Client\Domain\Filters
 */
class QueryFilter extends AbstractFilter {

	/**
	 * @var string
	 */
	protected $filterString;

	/**
	 * @var string $queryString
	 * @Assert\Type(type="string", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $queryString;

	/**
	 * @var string $queryFields
	 * @Assert\Type(type="string", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $queryFields;

	/**
	 * @param string $queryFields
	 */
	public function setQueryFields($queryFields) {
		$this->queryFields = $queryFields;
	}

	/**
	 * @return string
	 */
	public function getQueryFields() {
		return $this->queryFields;
	}

	/**
	 * @param string $queryString
	 */
	public function setQueryString($queryString) {
		$this->queryString = $queryString;
	}

	/**
	 * @return string
	 */
	public function getQueryString() {
		return $this->queryString;
	}

	/**
	 * @return string
	 */
	public function getFilterString() {
		if (!empty($this->queryString)) {
			$this->filterString = sprintf("&query=%s", rawurlencode($this->getQueryString()));
		}
		if (!empty($this->queryFields)) {
			$this->filterString .= sprintf("&queryFields=%s", rawurlencode($this->getQueryFields()));
		}
		return $this->filterString;
	}
}