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

}
