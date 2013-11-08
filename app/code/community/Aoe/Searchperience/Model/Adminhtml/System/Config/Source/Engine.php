<?php
/**
 * Description of Engine
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Model_Adminhtml_System_Config_Source_Engine extends Enterprise_Search_Model_Adminhtml_System_Config_Source_Engine
{
    /**
     * @return array options for select field
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        $options[] = array(
            'value' => 'aoe_searchperience/engine',
            'label' => Mage::helper('aoe_searchperience')->__('Searchperience')
        );
        return $options;
    }
}
