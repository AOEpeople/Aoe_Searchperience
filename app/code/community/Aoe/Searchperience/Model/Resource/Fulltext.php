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
     * Regenerate search index for specific store
     *
     * @param int $storeId Store View Id
     * @param int|array $productIds Product Entity Id
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _rebuildStoreIndex($storeId, $productIds = null)
    {
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

        $lastProductId = 0;
        while (true) {
            $products = $this->_getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);
            if (!$products) {
                break;
            }

            $productAttributes = array();
            $productRelations  = array();
            foreach ($products as $productData) {
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

            $productIndexes    = array();
            $productAttributes = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);
            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];
                $hasParent   = true;

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

            // cleanup
            self::$dateTimeAttributeValues = array();
        }

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
     * Retrieve searchable products per store
     *
     * @param int $storeId
     * @param array $staticFields
     * @param array|int $productIds
     * @param int $lastProductId
     * @param int $limit
     * @return array
     */
    protected function _getSearchableProducts($storeId, array $staticFields, $productIds = null, $lastProductId = 0,
        $limit = 100)
    {
        $websiteId      = Mage::app()->getStore($storeId)->getWebsiteId();
        $writeAdapter   = $this->_getWriteAdapter();

        $select = $writeAdapter->select()
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->getTable('catalog/product')),
                array_merge(array('entity_id', 'type_id'), $staticFields)
            )
            ->join(
                array('website' => $this->getTable('catalog/product_website')),
                $writeAdapter->quoteInto(
                    'website.product_id=e.entity_id AND website.website_id=?',
                    $websiteId
                ),
                array()
            );

        if (!is_null($productIds)) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $select->where('e.entity_id>?', $lastProductId)
            ->limit($limit)
            ->order('e.entity_id');

        $result = $writeAdapter->fetchAll($select);

        return $result;
    }
}
