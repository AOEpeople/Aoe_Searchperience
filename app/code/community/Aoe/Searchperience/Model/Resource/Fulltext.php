<?php

class Aoe_Searchperience_Model_Resource_Fulltext {

    /**
     * Regenerate search index for store(s)
     *
     * @param  int|null $storeId
     * @param  int|array|null $productIds
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function rebuildIndex($storeId=null, $productIds=null)
    {
        if ($productIds === array()) {
            return $this; // no products will be found anyways. In order to reindex all pass null instead of array()
        }

        $storeIds = is_null($storeId) ? array_keys(Mage::app()->getStores()) : array($storeId);
        foreach ($storeIds as $storeId) {
            if (!Mage::getStoreConfigFlag('searchperience/searchperience/enablePushingDocumentsToSearchperience', $storeId)) {
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('Skipping indexing for store "%s" because of enablePushingDocumentsToSearchperience', $storeId), Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
                continue;
            }

            $this->_rebuildStoreIndex($storeId, $productIds);
        }
        return $this;
    }

    /**
     * Regenerate search index for specific store
     *
     * @param int $storeId Store View Id
     * @param int|array $requestedProductIds Product Entity Id
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _rebuildStoreIndex($storeId, $requestedProductIds=null)
    {
        $documentCreator = Mage::getModel('aoe_searchperience/productDocumentCreator'); /* @var $documentCreator Aoe_Searchperience_Model_ProductDocumentCreator */
        $searchperienceApi = Mage::getModel('aoe_searchperience/searchperienceApi'); /* @var $searchperienceApi Aoe_Searchperience_Model_SearchperienceApi */
        $sourceIdentifier = Mage::getStoreConfig('searchperience/searchperience/source');

        $limit = max(1, min(5000, Mage::getStoreConfig('searchperience/searchperience/indexerBatchSize')));

        $lastProductId = 0;
        $productsProcessed = array();
        while (true) {

            // go find some products...
            $productIds = $this->getSearchableProducts($storeId, $requestedProductIds, $lastProductId, $limit);
            if (!$productIds) { break; }

            Mage::helper('aoe_searchperience/log')->log_getSearchableProducts($productIds, $requestedProductIds, $storeId);

            // remember the last product id for the next loop iteration...
            $lastProductId = end($productIds);

            // get parents and children
            $childProductIds = $this->getChildIds($productIds);
            $parentProductIds = $this->getChildIds($productIds);

            // merge products, children and parents
            $productIds = array_merge($productIds, $childProductIds, $parentProductIds);

            // remove any duplicates
            $productIds = array_unique($productIds);

            // remove the ones that have been processed before
            $productIds = array_diff($productIds, $productsProcessed);

            // process products...
            foreach ($productIds as $productId) {
                $data = $documentCreator->createDocument($productId, $storeId);

                if ($data === false) {
                    // this product will not be indexed
                    continue;
                }

                if (empty($data['url'])) { Mage::throwException('No product url found'); }
                if (empty($data['raw_document'])) { Mage::throwException('No raw_document found'); }

                echo $data['raw_document'];

//                $searchperienceApi->addDocument(
//                    $data['raw_document'],
//                    $productId . '_' . $storeId,
//                    $data['url'],
//                    $sourceIdentifier
//                );
            }

            // add new products to the processed ones
            $productsProcessed = array_merge($productsProcessed, $productIds);
        }

        // delete missing products
        if (!is_null($requestedProductIds)) {
            $missingProducts = array_diff($requestedProductIds, $productsProcessed);
            if (count($missingProducts)) {
                $this->cleanIndex($storeId, $missingProducts);
            }
        }
        exit;
        return $this;
    }

    /**
     * Get parent ids
     *
     * @param array $productIds
     * @return array
     */
    protected function getParentIds(array $productIds)
    {
        return $this->getDbConnection()->select()
            ->from($this->getTable('catalog/product_relation'), 'parent_id')
            ->distinct(true)
            ->where('child_id IN (?)', $productIds)
            ->where('parent_id NOT IN (?)', $productIds)
            ->query()->fetchAll(Zend_Db::FETCH_COLUMN);
    }

    /**
     * Get child ids
     *
     * @param array $productIds
     * @return array
     */
    protected function getChildIds(array $productIds)
    {
        return $this->getDbConnection()->select()
            ->from($this->getTable('catalog/product_relation'), 'parent_id')
            ->distinct(true)
            ->where('parent_id IN (?)', $productIds)
            ->where('child_id NOT IN (?)', $productIds)
            ->query()->fetchAll(Zend_Db::FETCH_COLUMN);
    }

    /**
     * Get DB connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function getDbConnection() {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }


    /**
     * Get searchable products
     *
     * @param $storeId
     * @param null $productIds
     * @param int $lastProductId
     * @param int $limit
     * @return array
     */
    protected function getSearchableProducts($storeId, $productIds=null, $lastProductId = 0, $limit = 100) {
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $connection = $this->getDbConnection();

        $select = $connection->select()
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->getTable('catalog/product')),
                array('entity_id')
            )
            ->join(
                array('website' => $this->getTable('catalog/product_website')),
                $connection->quoteInto('website.product_id=e.entity_id AND website.website_id=?', $websiteId),
                array()
            );

        if (!is_null($productIds)) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $select->where('e.entity_id>?', $lastProductId)
            ->limit($limit)
            ->order('e.entity_id');

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('website.website_id'),
            'store_field'   => $storeId
        ));

        return $connection->fetchCol($select);
    }

    /**
     * Get tablename
     *
     * @param $table
     * @return mixed
     */
    protected function getTable($table) {
        // since catalogsearch/fulltext is $this but $this doesn't extend from the original class this is a little trick...
        return Mage::getResourceSingleton('catalog/product')->getTable($table);
    }

}
