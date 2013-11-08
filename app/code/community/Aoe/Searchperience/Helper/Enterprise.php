<?php
/**
 * Rewrite for the Enterprise helper
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Fabrizio Branca
 */
class Aoe_Searchperience_Helper_Enterprise extends Enterprise_CatalogSearch_Helper_Data
{

    /**
     * Return whether fulltext engine is on
     *
     * @return bool
     */
    public function isFulltextOn()
    {
        $searchEngine = (string) Mage::getStoreConfig('catalog/search/engine');
        return parent::isFulltextOn() || $searchEngine == 'aoe_searchperience/engine';
    }

}
