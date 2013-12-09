<?php

class Aoe_Searchperience_Model_Adapter_Searchperience extends Enterprise_Search_Model_Adapter_Solr_Abstract
{
    /**
     * Api document class name
     *
     * @var string
     */
    protected $_clientDocObjectName = 'Aoe_Searchperience_Model_Api_Document';

    /**
     * @var array of categories $categories[store][categoryId]=>category data
     */
    protected $categories = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        try {
            $this->_connect($options);
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException('Unable to perform search because of search engine missed configuration.');
        }
    }

    /**
     * Connect to Search Engine Client by specified options.
     * Should initialize _client
     *
     * @param array $options
     * @return \Mage_Core_Model_Abstract
     */
    protected function _connect($options = array())
    {
        $this->_client = Mage::getSingleton('aoe_searchperience/client_searchperience', $options);
        return $this->_client;
    }

    /**
     * Simple Search interface
     *
     * @param string $query
     * @param array $params
     */
    protected function _search($query, $params = array())
    {
        // TODO: Implement _search() method.
    }

    /**
     * Create Solr Input Documents by specified data
     *
     * @param   array $docData
     * @param   int $storeId
     *
     * @return  array
     */
    public function prepareDocsPerStore($docData, $storeId)
    {
        if (!is_array($docData) || empty($docData)) {
            return array();
        }
        Varien_Profiler::start(__CLASS__.__METHOD__);
        $docs = array();
        foreach ($docData as $productId => $productIndexData) {
            $doc = new $this->_clientDocObjectName;
            $productIndexData = $this->_prepareIndexProductData($productIndexData, $productId, $storeId);

            if (!$productIndexData) {
                continue;
            }

            $doc->setData($productIndexData);
            $docs[] = $doc;
          unset($docData[$productId]);
        }
        Varien_Profiler::stop(__CLASS__.__METHOD__);
        return $docs;
    }

    /**
     * Remove documents from Solr index
     *
     * @param  int|string|array $docIDs
     * @param  string|array|null $queries if "all" specified and $docIDs are empty, then all documents will be removed
     * @return Aoe_Searchperience_Model_Adapter_Searchperience
     */
    public function deleteDocs($docIDs = array(), $queries = null)
    {
        // check if deletion of documents is allowed
        if (!Mage::helper('aoe_searchperience')->isDeletionEnabled()) {
            return $this;
        }

        foreach ($queries as $query) {
            try {
                $this->_client->getDocumentRepository()->deleteByForeignId($query);

                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('successfully deleted document with foreign id %s from repository', $query));
                }
            } catch (Exception $e) {}
        }
        return $this;
    }

    /**
     * @param $availability
     * @return string
     */
    protected function getAvailabilitySpeakingName($availability) {
        switch(filter_var($availability, FILTER_VALIDATE_INT)) {
            case 0: return 'out_of_stock';
            case 1: return 'in_stock';
            default: return $availability;
        }
    }

    /**
     * Prepare index data for using in search engine metadata.
     * Prepare fields for advanced search, navigation, sorting and fulltext fields for each search weight for
     * quick search and spell.
     *
     * @param array $productIndexData
     * @param int $productId
     * @param int $storeId
     *
     * @return  array|bool
     */
    protected function _prepareIndexProductData($productIndexData, $productId, $storeId)
    {
        Varien_Profiler::start(__CLASS__.__METHOD__);
        $searchperienceHelper = Mage::helper('aoe_searchperience');

        if (!$this->isAvailableInIndex($productIndexData, $productId)) {
            return false;
        }

        $returnData  = array(
            'storeid'  => $storeId,
            'language' => Mage::getStoreConfig('general/locale/code', $storeId),
            'in_stock' => $this->getAvailabilitySpeakingName(isset($productIndexData['in_stock']) ? $productIndexData['in_stock'] : ''),
        );

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('product');

        // product not found in registry or is not equal to given productId, load from database
        if ((null === $product) || ($product->getId() != $productId)) {
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
        }

        // fetch review data for requested product
        Mage::getModel('review/review')->getEntitySummary($product, $storeId);

        $returnData['productData']['id']     = $productId;
        $returnData['productData']['sku']    = $productIndexData['sku'];
        $returnData['productData']['url']    = $product->getProductUrl();
        $returnData['productData']['unique'] = $searchperienceHelper->getProductUniqueId($productId, $storeId);
        $returnData['productData']['rating'] = $product->getRatingSummary()->getRatingSummary();

        $this->_usedFields   = array_merge($this->_usedFields, array('id', 'description', 'short_description', 'price', 'name', 'tax_class_id'));

        // fetch price information
        $returnData = $this->_getProductPriceInformation($product, $returnData);

        // fetch image information
       $returnData = $this->_getProductImageInformation($product, $returnData);

        $returnData = $this->fillProductCategoryInformation($product, $returnData);

        // fetch related products
        foreach ($this->getLinkedProductIds($productId) as $relatedProduct) {
            $returnData['productData']['related'][] = $relatedProduct;
        }

        // fetch upsell products
        foreach ($this->getLinkedProductIds($productId, Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL) as $upsellProduct) {
            $returnData['productData']['upsell'][] = $upsellProduct;
        }

        // fetch crosssell products
        foreach ($this->getLinkedProductIds($productId, Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL) as $crossProduct) {
            $returnData['productData']['cross'][] = $crossProduct;
        }

        foreach ($productIndexData as $attributeCode => $value) {
            if ($this->_skipAttribute($attributeCode)) {
                continue;
            }

            if (is_array($value)) {
                $returnData['productData'][$attributeCode] = $value[$productId];
            }
            else {
                $returnData[$attributeCode] = $value;
            }
        }

        // fetch additional product information
        list($dynamicFields, $usedForSorting, $usedForFiltering, $attributeTypes) = $this->_getAdditionalProductData($productIndexData, $productId, $storeId);

        $returnData['productData']['additionalData'] = $dynamicFields;
        $returnData['attributesUsedForSorting'] = $usedForSorting;
        $returnData['attributesUsedForFiltering'] = $usedForFiltering;
        $returnData['attributeTypes'] = $attributeTypes;

        $options = new Varien_Object();
        $options->setIndexData($returnData);
        $options->setProduct($product);

        Mage::dispatchEvent(
            'aoe_searchperience_prepareIndexProductData_after',
            array('adapter' => $this, 'options' => $options)
        );
        Varien_Profiler::stop(__CLASS__.__METHOD__);
        $returnData = $options->getIndexData();
        unset($product, $options, $productIndexData, $dynamicFields, $usedForSorting, $usedForFiltering, $attributeTypes);
        return $returnData;
    }

    /**
     * Returns array of related products ids
     * @param $productId
     * @param int $relationType
     * @param int $limit
     * @return mixed
     */
    protected function getLinkedProductIds($productId, $relationType=Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED, $limit=10) {
        Varien_Profiler::start(__CLASS__.__METHOD__.$relationType);
        /** @var $linkModel Mage_Catalog_Model_Product_Link */
        $linkModel = Mage::getSingleton('catalog/product_link');
        $collection = $linkModel->setLinkTypeId($relationType)->getLinkCollection();
        $collection->addFieldToFilter('product_id',  array('eq' => $productId))
            ->addLinkTypeIdFilter()
            ->addFieldToSelect('linked_product_id')
            ->setPageSize($limit)->setCurPage(1);
        $linkedProductIds = $collection->getColumnValues('linked_product_id');
        Varien_Profiler::stop(__CLASS__.__METHOD__.$relationType);
        return $linkedProductIds;
    }

    /**
     * Get additional product data
     *
     * @param array $productIndexData
     * @param $productId
     * @param $storeId
     * @return array
     */
    protected function _getAdditionalProductData($productIndexData, $productId, $storeId)
    {
        Varien_Profiler::start(__CLASS__.__METHOD__);
        $usedForSorting   = array();
        $usedForFiltering = array();
        $attributeTypes = array();

        foreach ($productIndexData as $attributeCode => $attributeValue) {

            if ($attributeCode == 'visibility') {
                $productIndexData[$attributeCode] = $attributeValue[$productId];
                continue;
            }

            // Prepare processing attribute info
            if (isset($this->_indexableAttributeParams[$attributeCode])) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $attribute = $this->_indexableAttributeParams[$attributeCode];
            } else {
                $attribute = null;
            }

            // Prepare values for required fields
            if (in_array($attributeCode, $this->_usedFields)) {
                unset($productIndexData[$attributeCode]);
                continue;
            }

            if (!$attribute || $attributeCode == 'price' || empty($attributeValue)) {
                continue;
            }

            $attribute->setStoreId($storeId);
            $preparedValue = array();

            // Preparing data for solr fields
            if ($attribute->getIsSearchable() || $attribute->getIsVisibleInAdvancedSearch()
                || $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()
                || $attribute->getUsedForSortBy()
            ) {

                $backendType = $attribute->getBackendType();
                $frontendInput = $attribute->getFrontendInput();

                if ($attribute->usesSource()) {
                    if ($frontendInput == 'multiselect') {
                        foreach ($attributeValue as $val) {
                            $preparedValue = array_merge($preparedValue, explode(',', $val));
                        }
                        $preparedNavValue = $preparedValue;
                    } else {
                        // safe condition
                        if (!is_array($attributeValue)) {
                            $preparedValue = array($attributeValue);
                        } else {
                            $preparedValue = array_unique($attributeValue);
                        }

                        $preparedNavValue = $preparedValue;
                        // Ensure that self product value will be saved after array_unique function for sorting purpose
                        if (isset($attributeValue[$productId])) {
                            if (!isset($preparedNavValue[$productId])) {
                                $selfValueKey = array_search($attributeValue[$productId], $preparedNavValue);
                                unset($preparedNavValue[$selfValueKey]);
                                $preparedNavValue[$productId] = $attributeValue[$productId];
                            }
                        }
                    }

                    foreach ($preparedValue as $id => $val) {
                        $preparedValue[$id] = $attribute->getSource()->getOptionText($val);
                    }
                } else { // no source
                    if ($backendType == 'datetime') {
                        if (is_array($attributeValue)) {
                            foreach ($attributeValue as &$val) {
                                $val = Mage::helper('aoe_searchperience')->getTimestampForAttribute(
                                    $storeId,
                                    $val,
                                    $attributeCode
                                );
                                if (!empty($val)) {
                                    $preparedValue[] = $val;
                                }
                            }
                            unset($val); //clear link to value
                            $preparedValue = array_unique($preparedValue);
                        } else {
                            $preparedValue = Mage::helper('aoe_searchperience')->getTimestampForAttribute(
                                $storeId,
                                $attributeValue,
                                $attributeCode
                            );
                        }
                    }
                }
            }

            if ($attribute->getUsedForSortBy()) {
                $usedForSorting[$attributeCode] = 1;
            }

            if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
                $usedForFiltering[$attributeCode] = 1;
            }

            $attributeTypes[$attributeCode]   = $this->getAttributeSearchType($attribute);
            $productIndexData[$attributeCode] = $preparedValue;

            unset($preparedNavValue, $preparedValue, $fieldName, $attribute);
        }
        Varien_Profiler::stop(__CLASS__.__METHOD__);
        return array($productIndexData, $usedForSorting, $usedForFiltering, $attributeTypes);
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute|string $attribute
     * @return string
     */
    private function getAttributeSearchType($attribute)
    {
        if (is_string($attribute)) {
            if ($attribute == 'price') {
                return 'float';
            }

            $eavConfig  = Mage::getSingleton('eav/config');
            $entityType = $eavConfig->getEntityType('catalog_product');
            $attribute  = $eavConfig->getAttribute($entityType, $attribute);
        }
        $attributeCode = $attribute->getAttributeCode();
        if ($attributeCode == 'price') {
            return $this->getPriceFieldName();
        }

        $backendType    = $attribute->getBackendType();
        $frontendInput  = $attribute->getFrontendInput();

        if ($frontendInput == 'multiselect') {
            $fieldType = 'string';
        } elseif ($frontendInput == 'select') {
            $fieldType = 'string';
        } elseif ($frontendInput == 'boolean') {
            $fieldType = 'string';
        } elseif ($backendType == 'decimal') {
            $fieldType = 'float';
        } elseif ($backendType == 'varchar') {
            $fieldType = 'string';
        } elseif ($backendType == 'datetime') {
            $fieldType = 'date';
        } else {
            $fieldType = 'text';
        }

        return $fieldType;
    }

    /**
     * Fetches all categories to local cache
     * @param $storeId
     */
    protected function fetchCategories($storeId) {
        if(!isset($this->categories[$storeId])) {
            $this->categories[$storeId] = array();
        }

        /** @var $categoryCollection Mage_Catalog_Model_Resource_Category_Collection */
        $categoryCollection = Mage::getResourceModel('catalog/category_collection');
        $categoryCollection->setStoreId($storeId)
            ->addNameToResult()
            ->addIsActiveFilter()
            ->setLoadProductCount(FALSE);
        $categories = $categoryCollection->load()->toArray(array('path','level','name'));
        unset($categoryCollection);

        $this->categories[$storeId] = $categories;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param array $data
     * @return mixed
     */
    protected function fillProductCategoryInformation($product, $data) {
        $skippableCategories = array();
        if (($skipCategories = Mage::getStoreConfig('searchperience/searchperience/skipCategories'))) {
            $skippableCategories = array_map('trim', explode(',', $skipCategories));
        }
        $storeId = $product->getStoreId();
        $this->fetchCategories($storeId);

        // fetch category information
        foreach ($product->getCategoryIds() as $categoryId) {
            if (!isset($returnData['categories'][$categoryId]) && isset($this->categories[$storeId][$categoryId])) {
                $category = $this->categories[$storeId][$categoryId];
                $data['categories'][$categoryId]['name'] = $category['name'];

                $pathCategories = explode('/', $category['path']);
                $path = array();
                //don't need root category
                array_shift($pathCategories);
                foreach($pathCategories as $pathCategoryId) {
                    // do not include skippable categories in list
                    if (in_array($pathCategoryId, $skippableCategories)) {
                        unset($data['categories'][$categoryId]);
                        continue 2;
                    }
                    $cat = $this->categories[$storeId][$pathCategoryId];
                    if ($cat['level'] > 1) {
                        $pathPart = $cat['name'];
                        $pathPart = str_replace('/','&#47;', $pathPart);
                        $path[] = $pathPart;
                    }
                }
                $data['categories'][$categoryId]['path'] = implode('/', $path);
            }
        }
        return $data;
    }

    /**
     * Get price information for product
     *
     * @param $product Mage_Catalog_Model_Product
     * @param array $data
     * @return array
     */
    protected function _getProductPriceInformation($product, $data)
    {
        // define attributes and get methods
        $attributes = array(
            'special_price' => 'getSpecialPrice',
            // @TODO: make group prices work!
            'group_price'   => 'getGroupPrice',
        );
        foreach ($attributes as $attributeCode => $getMethod) {
            if (!empty($this->_indexableAttributeParams[$attributeCode])) {
                $value = $product->$getMethod();
                if($value) {
                    $data['productData'][$attributeCode] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Get product image information
     *
     * @param $product Mage_Catalog_Model_Product
     * @param array $data
     * @return array
     */
    protected function _getProductImageInformation($product, $data)
    {
        $width  = Mage::getStoreConfig('searchperience/searchperience/listViewImageWidth');
        $height = Mage::getStoreConfig('searchperience/searchperience/listViewImageHeight');

        // define attributes and get methods
        $attributes = array(
            'thumbnail'   => 'getThumbnailUrl',
            'image'       => 'getImageUrl',
            'small_image' => 'getSmallImageUrl',
        );

        foreach ($attributes as $attributeCode => $getMethod) {
          $data['productData']['images'][$attributeCode] = $product->$getMethod($width, $height);
        }
       return $data;
    }

    /**
     * Checks, if given attribute shall be skipped for indexing
     *
     * @param $attributeCode
     * @return boolean
     */
    protected function _skipAttribute($attributeCode)
    {
        // not in the list of searchable attributes
        if (!in_array($attributeCode, array_keys($this->_indexableAttributeParams))) {
            return true;
        }

        // is user defined
        if (
            isset($this->_indexableAttributeParams[$attributeCode]) &&
            1 == $this->_indexableAttributeParams[$attributeCode]->isUserDefined
        ) {
            return true;
        }

        return false;
    }
}
