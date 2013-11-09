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
        // Reindex all products
        $this->getFulltextResource()->rebuildIndex();
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
