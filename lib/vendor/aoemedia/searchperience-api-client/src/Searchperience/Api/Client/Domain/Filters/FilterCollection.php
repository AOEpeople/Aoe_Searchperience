<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;


/**
 * Class FiltersCollection
 * @package Searchperience\Api\Client\Domain\Filters
 */
class FilterCollection extends \ArrayObject {

	/**
	 * @var ArrayObject
	 */
	protected $filters;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->filters = new \ArrayObject();
	}

	/**
	 * @object $filter
	 * @throws \Searchperience\Common\Exception\UnexpectedValueException
	 */
	public function addFilter($filter){
		$this->filters->append($filter);
	}

	/**
	 * @return string
	 */
	public function getFilterStringFromAll() {
		$filtersString = '';

		foreach ($this->filters as $key => $filterObject) {
			$filtersString .= $filterObject->getFilterString();
		}

		return $filtersString;
	}

	/**
	 * @return int
	 */
	public function getCount() {
		return $this->filters->count();
	}

}