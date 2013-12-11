<?php

/**
 * Class Aoe_Searchperience_Model_Indexer_Fulltext
 *
 * @author Fabrizio Branca
 * @since 2013-11-08
 */
class Aoe_Searchperience_Model_Indexer_Fulltext extends Mage_CatalogSearch_Model_Indexer_Fulltext
{

    /**
     * @var array forces skipping all index actions on save
     */
    protected $_matchedEntities = array();

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('catalogsearch')->__('Searchperience Index');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('catalogsearch')->__('Pushes product updates to Searchperience');
    }

    /**
     * Rebuild all index data
     * (but we don't need to do this in a transaction)
     *
     * @see parent method
     */
    public function reindexAll()
    {

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            Mage::log('Full reindex started', Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
        }

        $this->_getIndexer()->rebuildIndex();

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            Mage::log('Full reindex finished', Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
        }
    }

}
