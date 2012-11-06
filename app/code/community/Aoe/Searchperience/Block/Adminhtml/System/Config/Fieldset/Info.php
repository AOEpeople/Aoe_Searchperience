<?php
/**
 * Rendering class for searchperience info panel @ magento config page
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Block_Adminhtml_System_Config_Fieldset_Info
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'searchperience/system/config/fieldset/info.phtml';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
