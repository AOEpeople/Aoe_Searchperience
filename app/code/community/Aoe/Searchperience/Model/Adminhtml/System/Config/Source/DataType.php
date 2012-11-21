<?php
/**
 * Description of DataType
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Model_Adminhtml_System_Config_Source_DataType
{
    /**
     * @return array options for select field
     */
    public function toOptionArray()
    {
        $searchperienceHelper = Mage::helper('aoe_searchperience'); /* @var $searchperienceHelper Aoe_Searchperience_Helper_Data */

        $options = array(
            array('value' => 'jsonp', 'label' => $searchperienceHelper->__('JSONP')),
            array('value' => 'html', 'label' => $searchperienceHelper->__('HTML'))
        );

        return $options;
    }
}
