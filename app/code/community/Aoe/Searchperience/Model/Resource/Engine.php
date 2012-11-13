<?php

class Aoe_Searchperience_Model_Resource_Engine extends Enterprise_Search_Model_Resource_Engine
{
    /**
     * Store search engine adapter model instance
     *
     * @var Enterprise_Search_Model_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Initialize search engine adapter
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    protected function _initAdapter()
    {
        $this->_adapter = $this->_getAdapterModel();

        return $this;
    }

    /**
     * Set search engine adapter
     */
    public function __construct()
    {
        $this->_initAdapter();
    }

    /**
     * Retrieve search engine adapter model
     *
     * @return Aoe_Searchperience_Model_Adapter_Solr
     */
    protected function _getAdapterModel()
    {
        return Mage::getSingleton('aoe_searchperience/adapter_searchperience');
    }
}
