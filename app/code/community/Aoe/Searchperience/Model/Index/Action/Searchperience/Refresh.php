<?php

/**
 * Class Aoe_Searchperience_Model_Index_Action_Searchperience_Refresh
 *
 * @author Fabrizio Branca
 * @since 2013-11-08
 */
class Aoe_Searchperience_Model_Index_Action_Searchperience_Refresh extends Aoe_Searchperience_Model_Index_Action_Searchperience_Action
{

    /**
     * @var Aoe_Searchperience_Model_Resource_Fulltext
     */
    protected $_fulltextResource;


    protected function _execute()
    {

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            Mage::log('Full reindex started (refresh)', Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
        }

        // Reindex all products
        $this->getFulltextResource()->rebuildIndex();

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            Mage::log('Full reindex finished (refresh)', Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
        }
    }

    /**
     * @return Aoe_Searchperience_Model_Resource_Fulltext
     */
    protected function getFulltextResource() {
        if (is_null($this->_fulltextResource)) {
            $this->_fulltextResource = Mage::getResourceModel('aoe_searchperience/fulltext');
        }
        return $this->_fulltextResource;
    }

}
