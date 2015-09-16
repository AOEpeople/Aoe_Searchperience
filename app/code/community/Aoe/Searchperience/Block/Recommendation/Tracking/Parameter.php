<?php

/**
 * Class Aoe_Searchperience_Block_Recommendation_Tracking_Parameter
 *
 * @category Aoe
 * @package  Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
class Aoe_Searchperience_Block_Recommendation_Tracking_Parameter extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $_template = 'aoesearchperience/recommendation/tracking/parameter.phtml';

    /**
     * @var array
     */
    protected $_parameter = [];

    /**
     * @var int
     */
    protected $_paramObjectNumber = 1;

    /**
     * @var array
     */
    protected $_skippableData = ['cache_lifetime', 'type', 'module_name', 'paramObject'];

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(
            [
                'cache_lifetime' => 86400,
            ]
        );
    }

    /**
     * Clean up internal data and add to parameter list
     *
     * @return array
     */
    public function getParameter()
    {
        if (empty($this->_parameter)) {
            foreach ($this->getData() as $key => $index) {
                if (in_array($key, $this->_skippableData)) {
                    continue;
                }

                $this->_parameter[$key] = $index;
            }
        }

        return $this->_parameter;
    }

    /**
     * add SKU of current product to parameter object
     *
     * @return void
     */
    public function addItem()
    {
        $currentProduct = Mage::registry('current_product');

        if (!is_null($currentProduct)) {
            $this->setData('item', $currentProduct->getSku());
        }
    }

    /**
     * Sets parameter object number
     *
     * @param  int $objectNumber Number of parameter object
     * @return void
     */
    public function setParamObjectNumber($objectNumber)
    {
        $this->_paramObjectNumber = $objectNumber;
    }

    /**
     * Returns parameter object number
     *
     * @return int
     */
    public function getParamObjectNumber()
    {
        return $this->_paramObjectNumber;
    }
}
