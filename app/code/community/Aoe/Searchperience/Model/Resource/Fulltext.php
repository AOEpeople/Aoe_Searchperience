<?php

class Aoe_Searchperience_Model_Resource_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{
    /**
     * Holds datetime attribute values
     * before and after modification
     *
     * @var array
     */
    public static $dateTimeAttributeValues = array();


    /**
     * Thread pool size
     *
     * @var int
     */
    protected $threadPoolSize = 1;

    /**
     * @var Threadi_Pool
     */
    protected $threadPool;

    /**
     * @var int thread counter
     */
    protected $threadCounter = 0;

    protected $threadBatchSize = 100;



    /**
     * Regenerate search index for specific store
     *
     * @param int $storeId Store View Id
     * @param int|array $productIds Product Entity Id
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _rebuildStoreIndex($storeId, $productIds = null)
    {

        if (!Mage::getStoreConfigFlag('searchperience/searchperience/enablePushingDocumentsToSearchperience', $storeId)) {
            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                Mage::log(sprintf('[Aoe_Searchperience] Skipping indexing for store "%s" because of enablePushingDocumentsToSearchperience', $storeId));
            }
            return;
        }

        // prepare searchable attributes
        $staticFields = array();
        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        $dynamicFields = array(
            'int'       => array_keys($this->_getSearchableAttributes('int')),
            'varchar'   => array_keys($this->_getSearchableAttributes('varchar')),
            'text'      => array_keys($this->_getSearchableAttributes('text')),
            'decimal'   => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime'  => array_keys($this->_getSearchableAttributes('datetime')),
        );

        require_once 'Threadi/Loader.php';

        $this->threadPool = new Threadi_Pool($this->threadPoolSize);

        $lastProductId = 0;
        while (true) {
            $products = $this->_getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId, $this->threadBatchSize);

            if (!$products) {
                break;
            }

            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                $message = sprintf('[Aoe_Searchperience] Found "%s" searchable products in store "%s".', count($products), $storeId);
                if (!is_null($productIds)) {
                    $message .= ' (productIds: ' . implode(', ',$productIds) . ')';
                }
                Mage::log($message);
            }

            $productAttributes = array();
            $productRelations  = array();
            foreach ($products as $productData) { /* @var $productData array */
                $lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChildren = $this->_getProductChildIds($productData['entity_id'], $productData['type_id']);
                $productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }

            // Wait until there is a free slot in the pool
            $this->threadPool->waitTillReady();

            // create new thread
            $this->threadCounter++;
            $thread = Threadi_ThreadFactory::getThread(array($this, 'processBatch'));

            if (!$thread instanceof Threadi_Thread_NonThread) {
                Mage::getSingleton('core/resource')->getConnection('core_write')->closeConnection();
                $this->_connections = array(); // delete cached connections

                if (class_exists('Enterprise_Index_Model_Lock')) {
                    Enterprise_Index_Model_Lock::getInstance()->shutdownReleaseLocks();
                }
            }

            $thread->start($storeId, $productIds, $productAttributes, $dynamicFields, $products, $productRelations);

            // append it to the pool
            $this->threadPool->add($thread);

            Mage::log('[Aoe_Searchperience] Starting a new thread: ' . $this->threadCounter);

            // $this->processBatch($storeId, $productIds, $productAttributes, $dynamicFields, $products, $productRelations);

            // cleanup
            self::$dateTimeAttributeValues = array();
        }

        $this->threadPool->waitTillAllReady();

        $this->resetSearchResults();

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            Mage::log('statistics: ' . var_export(Aoe_Searchperience_Model_Client_Searchperience::$statistics, true));
        }

        return $this;
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return string
     */
    protected function _prepareProductIndex($indexData, $productData, $storeId)
    {
        // store original values for later usage
        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {
                $attribute = $this->_getSearchableAttribute($attributeId);
                if ($attribute->getBackendType() == 'datetime') {
                    self::$dateTimeAttributeValues[$entityId][$attributeId] = $attributeValue;
                }
            }
        }

        return parent::_prepareProductIndex($indexData, $productData, $storeId);
    }

    /**
     * Retrieve attribute source value for search
     *
     * @param int $attributeId
     * @param mixed $value
     * @param int $storeId
     * @return mixed
     */
    protected function _getAttributeValue($attributeId, $value, $storeId)
    {
        if (is_string($value)) {
            $value = preg_replace('#<\s*br\s*/?\s*>#', ' ', $value);
        }

        return parent::_getAttributeValue($attributeId, $value, $storeId);
    }

    /**
     * Checks if product is visible
     *
     * @param   int     $productId
     * @param   array   $productAttributes
     * @return bool
     */
    protected function _isProductVisible($productId, $productAttributes)
    {
        $visibility              = $this->_getSearchableAttribute('visibility');
        $allowedVisibilityValues = $this->_engine->getAllowedVisibility();
        $productAttr             = $productAttributes[$productId];

        if (!isset($productAttr[$visibility->getId()])
            || !in_array($productAttr[$visibility->getId()], $allowedVisibilityValues)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if product is enabled
     *
     * @param $productId
     * @param $productAttributes
     * @return bool
     */
    protected function _isProductEnabled($productId, $productAttributes)
    {
        $status      = $this->_getSearchableAttribute('status');
        $statusVals  = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();
        $productAttr = $productAttributes[$productId];

        if (!isset($productAttr[$status->getId()]) || !in_array($productAttr[$status->getId()], $statusVals)) {
            return false;
        }

        return true;
    }

    /**
     * Checks, if given product is the child product of
     * determined relation structure and returns parent id
     *
     * @param $productRelations
     * @param $productId
     * @return int|bool
     */
    protected function _getParentProduct($productRelations, $productId)
    {
        // check if product is a parent product
        if (isset($productRelations[$productId])) {
            return false;
        }

        // no parent product, check if it is a child
        foreach ($productRelations as $parent => $listOfChildren) {
            if (is_array($listOfChildren) && in_array($productId, $listOfChildren)) {
                return $parent;
            }
        }

        return false;
    }

    /**
     * @param $storeId
     * @param $productIds
     * @param array $productAttributes
     * @param array $dynamicFields
     * @param array $products
     * @param array $productRelations
     * @return array
     */
    public function processBatch($storeId, $productIds, array $productAttributes, array $dynamicFields, array $products, array $productRelations)
    {
        $productIndexes = array();
        $productAttributes = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);
        foreach ($products as $productData) {
            if (!isset($productAttributes[$productData['entity_id']])) {
                continue;
            }

            $productAttr = $productAttributes[$productData['entity_id']];
            $hasParent = true;

            // determine has parent status and product id to process
            if (false == ($productId = $this->_getParentProduct($productRelations, $productData['entity_id']))) {
                $productId = $productData['entity_id'];
                $hasParent = false;
            }

            // only clean index, if (parent) product is visible and enabled
            if (
                !$this->_isProductVisible($productId, $productAttributes) ||
                !$this->_isProductEnabled($productId, $productAttributes)
            ) {
                $this->cleanIndex($storeId, $productIds);
                continue;
            }

            // only process products, which are parent products
            if (false !== $hasParent) {
                continue;
            }

            $productIndex = array(
                $productData['entity_id'] => $productAttr
            );

            if ($productChildren = $productRelations[$productData['entity_id']]) {
                foreach ($productChildren as $productChildId) {
                    if (isset($productAttributes[$productChildId])) {
                        $productIndex[$productChildId] = $productAttributes[$productChildId];
                    }
                }
            }
            $index = $this->_prepareProductIndex($productIndex, $productData, $storeId);

            $productIndexes[$productData['entity_id']] = $index;
        }

        $this->_saveProductIndexes($storeId, $productIndexes);
    }
}
