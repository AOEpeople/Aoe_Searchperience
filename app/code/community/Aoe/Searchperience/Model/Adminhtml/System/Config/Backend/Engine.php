<?php
/**
 * Catalog search backend model
 *
 * @category    Aoe_Seachperience
 * @package     Aoe_Seachperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Model_Adminhtml_System_Config_Backend_Engine extends Mage_Core_Model_Config_Data
{
    /**
     * After save call
     * Invalidate catalog search index if engine was changed
     *
     * @return Enterprise_Search_Model_Adminhtml_System_Config_Backend_Engine
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if ($this->isValueChanged()) {
            Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }
}
