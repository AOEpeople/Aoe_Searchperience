<?php
/**
 * Description of DataType
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Model_Adminhtml_System_Config_Source_Yesno
{
    /**
     * return 'true' and 'false' as string values
     *
     * @return array options for select field
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'false', 'label'=>Mage::helper('adminhtml')->__('No')),
            array('value' => 'true', 'label'=>Mage::helper('adminhtml')->__('Yes'))
        );
    }
}
