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

    /**
     * Remove entity data from search index
     *
     * For deletion of all documents parameters should be null. Empty array will do nothing.
     *
     * @param  int|array|null $storeIds
     * @param  int|array|null $entityIds
     * @param  string $entityType 'product'|'cms'
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function cleanIndex($storeIds = null, $entityIds = null, $entityType = 'product')
    {
        if ($storeIds === array() || $entityIds === array()) {
            return $this;
        }

        if (is_null($storeIds) || $storeIds == Mage_Core_Model_App::ADMIN_STORE_ID) {
            $storeIds = array_keys(Mage::app()->getStores());
        } else {
            $storeIds = (array) $storeIds;
        }

        $queries = array();
        if (!empty($entityIds)) {
            $entityIds = (array) $entityIds;
            foreach ($storeIds as $storeId) {
                foreach ($entityIds as $entityId) {
                    $queries[] = Mage::helper('aoe_searchperience')->getProductUniqueId($entityId, $storeId);
                }
            }
        }

        $this->_adapter->deleteDocs(array(), $queries);

        return $this;
    }
}
