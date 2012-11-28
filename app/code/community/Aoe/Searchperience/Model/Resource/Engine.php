<?php
//Strict Notice: Declaration of Aoe_Searchperience_Model_Resource_Engine::_getAdapterModel()
//    should be compatible with that of Enterprise_Search_Model_Resource_Engine::_getAdapterModel()
//    in /var/www/qvc/dev/.modman/Aoe_Searchperience/app/code/community/Aoe/Searchperience/Model/Resource/Engine.php on line 4
class Aoe_Searchperience_Model_Resource_Engine extends Enterprise_Search_Model_Resource_Engine
{
    /**
     * Store search engine adapter model instance
     *
     * @var Aoe_Searchperience_Model_Adapter_Searchperience
     */
    protected $_adapter = null;

    /**
     * Initialize search engine adapter
     *
     * @return Aoe_searchperience_Model_Resource_Engine
     */
    protected function _initAdapter()
    {
        $this->_adapter = $this->_getAdapterModel('');

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
     * Define if selected adapter is available
     *
     * @return bool
     */
    public function test() {

        return true;
    }

    /**
     * Retrieve search engine adapter model
     * @param string $adapterName
     * @return Aoe_Searchperience_Model_Adapter_Searchperience
     */
    protected function _getAdapterModel($adapterName)
    {
        return Mage::getSingleton('aoe_searchperience/adapter_searchperience');
    }
}
