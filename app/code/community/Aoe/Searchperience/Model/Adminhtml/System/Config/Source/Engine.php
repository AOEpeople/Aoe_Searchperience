<?php
/**
 * Description of Engine
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Model_Adminhtml_System_Config_Source_Engine
{
    /**
     * @see Enterprise_Search_Model_Adminhtml_System_Config_Source_Engine
     * @return array options for select field
     */
    public function toOptionArray()
    {
        $searchperienceHelper = Mage::helper('aoe_searchperience'); /* @var $searchperienceHelper Aoe_Searchperience_Helper_Data */

        $engines = array(
            'catalogsearch/fulltext_engine'      => Mage::helper('enterprise_search')->__('MySql Fulltext'),
            'aoe_searchperience/resource_engine' => $searchperienceHelper->__('Searchperience')
        );

        if ($searchperienceHelper->isEnterprise()) $engines['enterprise_search/engine'] = Mage::helper('enterprise_search')->__('Solr');

        $options = array();
        foreach ($engines as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        return $options;
    }
}
