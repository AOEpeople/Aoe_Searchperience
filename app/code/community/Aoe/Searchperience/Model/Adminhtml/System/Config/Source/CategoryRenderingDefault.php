<?php
/**
 * Description of DataType
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 */
class Aoe_Searchperience_Model_Adminhtml_System_Config_Source_CategoryRenderingDefault
{
    /**
     * @return array options for select field
     */
    public function toOptionArray()
    {
        /* @var $searchperienceHelper Aoe_Searchperience_Helper_Data */
        $searchperienceHelper = Mage::helper('aoe_searchperience');

        $options = array(
            array(
                'value' => 'default',
                'label' => $searchperienceHelper->__('Magento default')
            ),
            array(
                'value' => 'searchperience',
                'label' => $searchperienceHelper->__('Searchperience rendering'))
        );

        return $options;
    }
}
