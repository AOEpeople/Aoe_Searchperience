<?php

class Aoe_Searchperience_Model_Index_Action_Searchperience_Refresh_Changelog extends Aoe_Searchperience_Model_Index_Action_Searchperience_Refresh
{

    /**
     * Refresh rows by ids from changelog
     */
    protected function _execute()
    {
        $changeIds = $this->getChangeIds();

        if (count($changeIds)) {

            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                Mage::log(sprintf('Changelog reindex started for %s changelog items', count($changeIds)), Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
            }

            // Index basic products
            $this->getFulltextResource()->rebuildIndex(null, $changeIds);

            // Index parent products
            $this->getFulltextResource()->rebuildIndex(null, $this->getParentIds($changeIds));

            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                Mage::log(sprintf('Changelog reindex finished for %s changelog items', count($changeIds)), Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
            }
        }
    }

    /**
     * Get parent ids
     *
     * @param array $productIds
     * @return array
     */
    protected function getParentIds(array $productIds)
    {
        return $this->_getWriteAdapter()->select()
            ->from($this->_getTable('catalog/product_relation'), 'parent_id')
            ->distinct(true)
            ->where('child_id IN (?)', $productIds)
            ->where('parent_id NOT IN (?)', $productIds)
            ->query()->fetchAll(Zend_Db::FETCH_COLUMN);
    }
}
