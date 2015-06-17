<?php
/**
 * @Author: Nikolay Diaur <nikolay.diaur@aoe.com>
 * @Date: 2/24/14
 * @Time: 6:19 PM
 */

namespace Searchperience\Api\Client\Domain\Filters;


use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class BoostFactorFilter
 * @package Searchperience\Api\Client\Domain\Filters
 */
class BoostFactorFilter extends AbstractFilter {

	/**
	 * @var string
	 */
	protected $filterString;

	/**
	 * @var string $boostFactorStart
	 * @Assert\Type(type="double", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $boostFactorStart;

	/**
	 * @var string $boostFactorEnd
	 * @Assert\Type(type="double", message="The value {{ value }} is not a valid {{ type }}.")
	 */
	protected $boostFactorEnd;

	/**
	 * @param string $boostFactorEnd
	 */
	public function setBoostFactorEnd($boostFactorEnd) {
		$this->boostFactorEnd = $boostFactorEnd;
	}

	/**
	 * @return string
	 */
	public function getBoostFactorEnd() {
		return $this->boostFactorEnd;
	}

	/**
	 * @param string $boostFactorStart
	 */
	public function setBoostFactorStart($boostFactorStart) {
		$this->boostFactorStart = $boostFactorStart;
	}

	/**
	 * @return string
	 */
	public function getBoostFactorStart() {
		return $this->boostFactorStart;
	}

	/**
	 * @return string
	 */
	public function getFilterString() {
		if (isset($this->boostFactorStart)) {
			$this->filterString = sprintf("&boostFactorStart=%s", rawurlencode($this->getBoostFactorStart()));
		}
		if (isset($this->boostFactorEnd)) {
			$this->filterString .= sprintf("&boostFactorEnd=%s", rawurlencode($this->getBoostFactorEnd()));
		}
		return $this->filterString;
	}
}