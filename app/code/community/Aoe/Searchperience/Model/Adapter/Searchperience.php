<?php

/**
 * Class Aoe_Searchperience_Model_Adapter_Searchperience
 *
 * @category Model
 * @package  Aoe_AttributeConfigurator
 * @author   AOE Magento Team <team-magento@aoe.com>
 */
class Aoe_Searchperience_Model_Adapter_Searchperience extends Enterprise_Search_Model_Adapter_Solr_Abstract
{
    /**
     * Api document class name
     *
     * @var string $_clientDocObjectName
     */
    protected $_clientDocObjectName = 'Aoe_Searchperience_Model_Api_Document';

    /**
     * @var array of categories $categories[store][categoryId]=>category data
     */
    protected $_categories = array();

    /**
     * @var array of indexable attributes
     */
    protected $_indexableAttributeParams = array();

    /**
     * Constructor
     *
     * @param array $options Options
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
     * @param array $options Options
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
     * @param string $query  Query
     * @param array  $params Params
     * @return void
     */
    protected function _search($query, $params = array())
    {
        // TODO: Implement _search() method.
    }

    /**
     * Create Solr Input Documents by specified data
     *
     * @param array $docData DocData
     * @param int   $storeId Store Id
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
     * @param int|string|array  $docIDs  Doc Ids
     * @param string|array|null $queries If "all" specified and $docIDs are empty, then all documents will be removed
     * @param string            $source  (if $queries == all)
     * @return Aoe_Searchperience_Model_Adapter_Searchperience
     */
    public function deleteDocs($docIDs = array(), $queries = null, $source=null)
    {
        // check if deletion of documents is allowed
        if (!Mage::helper('aoe_searchperience')->isDeletionEnabled()) {
            return $this;
        }

        /** @var Searchperience\Api\Client\Domain\DocumentRepository $documentRepository */
        $documentRepository = $this->_client->getDocumentRepository();

        if ($queries === 'all' && count($docIDs) == 0) {

            try {
                if (is_null($source)) {
                    $source = Mage::getStoreConfig('searchperience/searchperience/source');
                }
                $statusCode = $documentRepository->deleteBySource($source);

                if ($statusCode == 200) {
                    if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                        Mage::log(
                            sprintf(
                                'Successfully deleted all documents from repository for source "%s"',
                                $source
                            ),
                            Zend_Log::INFO,
                            Aoe_Searchperience_Helper_Data::LOGFILE
                        );
                    }
                } else {
                    if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                        Mage::log(
                            sprintf(
                                'Error while deleting all documents from repository for source "%s" (Status Code: "%s")',
                                $source,
                                $statusCode
                            ),
                            Zend_Log::ERR,
                            Aoe_Searchperience_Helper_Data::LOGFILE
                        );
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);

                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(
                        sprintf(
                            'Error while deleting all documents from repository for source "%s" (Message: "%s")',
                            $source,
                            $e->getMessage()
                        ),
                        Zend_Log::ERR,
                        Aoe_Searchperience_Helper_Data::LOGFILE
                    );
                }
            }
        } else {
            foreach ($docIDs as $docId) {
                try {
                    $documentRepository->deleteByForeignId($docId);

                    if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                        Mage::log(
                            sprintf(
                                'Successfully deleted document with foreign id %s from repository',
                                $docId
                            ),
                            Zend_Log::INFO,
                            Aoe_Searchperience_Helper_Data::LOGFILE
                        );
                    }
                } catch (Searchperience\Common\Http\Exception\DocumentNotFoundException $e) {
                    if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                        Mage::log(
                            sprintf(
                                'Document with foreign id %s not found',
                                $docId
                            ),
                            Zend_Log::INFO,
                            Aoe_Searchperience_Helper_Data::LOGFILE
                        );
                    }
                } catch (Exception $e) {
                    Mage::logException($e);

                    if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                        Mage::log(
                            sprintf(
                                'Error while deleting document with foreign id %s from repository',
                                $docId
                            ),
                            Zend_Log::ERR,
                            Aoe_Searchperience_Helper_Data::LOGFILE
                        );
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param mixed $availability Availability
     * @return string
     */
    protected function getAvailabilitySpeakingName($availability)
    {
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
     * @param array $productIndexData Product Index Data
     * @param int   $productId        Product Id
     * @param int   $storeId          Store Id
     * @return  array|bool
     */
    protected function _prepareIndexProductData($productIndexData, $productId, $storeId)
    {
        Varien_Profiler::start(__CLASS__.__METHOD__);
        $searchperienceHelper = Mage::helper('aoe_searchperience');

        if (!$this->isAvailableInIndex($productIndexData, $productId)) {
            return false;
        }

        $returnData = array(
            'storeid'  => $storeId,
            'language' => Mage::getStoreConfig('general/locale/code', $storeId),
            'in_stock' => $this->getAvailabilitySpeakingName(isset($productIndexData['in_stock']) ? $productIndexData['in_stock'] : ''),
        );

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('product');

        // product not found in registry or is not equal to given productId, load from database
        if ((null === $product) || ($product->getId() != $productId) || ($product->getStoreId() != $storeId)) {
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
        }

        // Set store as product store to avoid url param additions
        $currentStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($storeId);

        $returnData['productData']['id']     = $productId;
        $returnData['productData']['sku']    = $productIndexData['sku'];
        $returnData['productData']['url']    = $product->getProductUrl();
        $returnData['productData']['unique'] = $searchperienceHelper->getProductUniqueId($productId, $storeId);

        if (Mage::getStoreConfigFlag('searchperience/include_data/rating')) {
            // fetch review data for requested product
            Mage::getModel('review/review')->getEntitySummary($product, $storeId);
            $returnData['productData']['rating'] = $product->getRatingSummary()->getRatingSummary();
        }

        $this->_usedFields   = array_merge(
            $this->_usedFields,
            array('id', 'description', 'short_description', 'price', 'name', 'tax_class_id')
        );

        if (Mage::getStoreConfigFlag('searchperience/include_data/prices')) {
            // fetch price information
            $returnData = $this->_getProductPriceInformation($product, $returnData);
        }

        if (Mage::getStoreConfigFlag('searchperience/include_data/images')) {
            // fetch image information
            $returnData = $this->_getProductImageInformation($product, $returnData);
        }

        if (Mage::getStoreConfigFlag('searchperience/include_data/categories')) {
            $returnData = $this->fillProductCategoryInformation($product, $returnData);
        }

        if (Mage::getStoreConfigFlag('searchperience/include_data/related')) {
            // fetch related products
            foreach ($this->getLinkedProductIds($productId) as $relatedProduct) {
                $returnData['productData']['related'][] = $relatedProduct;
            }
        }

        if (Mage::getStoreConfigFlag('searchperience/include_data/upsell')) {
            // fetch upsell products
            foreach ($this->getLinkedProductIds($productId, Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL) as $upsellProduct) {
                $returnData['productData']['upsell'][] = $upsellProduct;
            }
        }

        if (Mage::getStoreConfigFlag('searchperience/include_data/cross')) {
            // fetch crosssell products
            foreach ($this->getLinkedProductIds($productId, Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL) as $crossProduct) {
                $returnData['productData']['cross'][] = $crossProduct;
            }
        }

        foreach ($productIndexData as $attributeCode => $value) {
            if ($this->_skipAttribute($attributeCode)) {
                continue;
            }

            if (is_array($value) && isset($value[$productId])) {
                $returnData['productData'][$attributeCode] = $value[$productId];
            } else {
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
        $options->setStoreId($storeId);

        // Reset store
        Mage::app()->setCurrentStore($currentStore);

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
     * @param mixed $productId    Product Id
     * @param int   $relationType Relation Type
     * @param int   $limit        Limit
     * @return mixed
     */
    protected function getLinkedProductIds($productId, $relationType=Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED, $limit=10)
    {
        Varien_Profiler::start(__CLASS__.__METHOD__.$relationType);
        /** @var Mage_Catalog_Model_Product_Link $linkModel */
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
     * @param array $productIndexData Product Index Data
     * @param mixed $productId        Product Id
     * @param int   $storeId          Store Id
     * @return array
     */
    protected function _getAdditionalProductData($productIndexData, $productId, $storeId)
    {
        Varien_Profiler::start(__CLASS__.__METHOD__);
        $usedForSorting = array();
        $usedForFiltering = array();
        $attributeTypes = array();

        foreach ($productIndexData as $attributeCode => $attributeValue) {

            if ($attributeCode == 'visibility') {
                $productIndexData[$attributeCode] = $attributeValue[$productId];
                $attributeTypes[$attributeCode] = 'float';
                continue;
            }

            // Prepare processing attribute info
            if (isset($this->_indexableAttributeParams[$attributeCode])) {
                /* @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
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
            $codeValue = array();

            // Preparing data for solr fields
            if ($attribute->getIsSearchable() || $attribute->getIsVisibleInAdvancedSearch()
                || $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()
                || $attribute->getUsedForSortBy()
            ) {

                $backendType = $attribute->getBackendType();
                $frontendInput = $attribute->getFrontendInput();

                if (
                    $attribute->usesSource() || $this->attributeIsBoolean($attribute)
                ) {
                    if ($frontendInput == 'multiselect') {
                        foreach ($attributeValue as $val) {
                            $preparedValue = array_merge($preparedValue, explode(',', $val));
                        }
                        $preparedNavValue = $preparedValue;
                    } else if ($this->attributeIsBoolean($attribute)) {
                        if (count($attributeValue)) {
                            $preparedValue = array_merge($preparedValue, $attributeValue);
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

                    if (!$this->attributeIsBoolean($attribute)) {
                        foreach ($preparedValue as $id => $val) {
                            $preparedValue[$id] = $attribute->getSource()->getOptionText($val);

                            // prepare admin labels codes
                            $attribute->setData('store_id', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
                            $codeValue[$id] = $attribute->getSource()->getOptionText($val);
                            $attribute->setData('store_id', $storeId);
                        }
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
                    } elseif (isset($attributeValue[$productId])) {
                        $preparedValue = $attributeValue[$productId];
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

            // add the admin option code values if they exist
            if (!empty($codeValue)) {
                $attributeCodeKey = $attributeCode . '_code';
                if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
                    $usedForFiltering[$attributeCodeKey] = 1;
                }
                $attributeTypes[$attributeCodeKey] = $attributeTypes[$attributeCode];
                $productIndexData[$attributeCodeKey] = $codeValue;
            }

            unset($preparedNavValue, $preparedValue, $fieldName, $attribute);
        }
        Varien_Profiler::stop(__CLASS__.__METHOD__);
        return array($productIndexData, $usedForSorting, $usedForFiltering, $attributeTypes);
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute|string $attribute Attribute
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
        $sourceModel    = $attribute->getSourceModel();

        if ($frontendInput == 'multiselect') {
            $fieldType = 'string';
        } elseif ($frontendInput == 'select' && $sourceModel == 'catalog/product_status') {
            // Product Status
            $fieldType = 'boolean';
        } elseif ($frontendInput == 'select') {
            $fieldType = 'string';
        } elseif ($frontendInput == 'boolean') {
            $fieldType = 'boolean';
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
     *
     * @param int $storeId Store Id
     * @return void
     */
    protected function fetchCategories($storeId)
    {
        if (isset($this->_categories[$storeId])) {
            return;
        }

        $this->_categories[$storeId] = [];

        /** @var Mage_Catalog_Model_Resource_Category_Collection $categoryCollection */
        $categoryCollection = Mage::getResourceModel('catalog/category_collection');
        $categoryCollection->setStoreId($storeId)
            ->addNameToResult()
            ->addFieldToFilter('is_active', ['in' => ['0', '1']])
            ->addAttributeToSelect('*')
            ->setLoadProductCount(false);

        $categories = [];
        foreach ($categoryCollection as $category) {
            /** @var Mage_Catalog_Model_Category $category */
            $categoryData = $category->toArray(['path','level','name', 'is_active']);
            $categoryData['anchorsAbove'] = $category->getAnchorsAbove();

            $categories[$category->getId()] = $categoryData;
        }
        unset($categoryCollection);

        $this->_categories[$storeId] = $categories;
    }

    /**
     * @param Mage_Catalog_Model_Product $product Product Model
     * @param array                      $data    Data
     * @return mixed
     */
    protected function fillProductCategoryInformation($product, $data)
    {
        $skippableCategories = array();
        if (($skipCategories = Mage::getStoreConfig('searchperience/searchperience/skipCategories'))) {
            $skippableCategories = array_map('trim', explode(',', $skipCategories));
        }
        $storeId = $product->getStoreId();
        $this->fetchCategories($storeId);

        // TODO: refactor using Aoe_Searchperience_Helper_Category

        // fetch category information
        foreach ($product->getCategoryIds() as $categoryId) {
            if (isset($data['categories'][$categoryId]) || !isset($this->_categories[$storeId][$categoryId])) {
                // category is already processed or not part of this store
                continue;
            }

            $category = $this->_categories[$storeId][$categoryId];
            $data['categories'][$categoryId]['name'] = $category['name'];
            $data['categories'][$categoryId]['is_active'] = $category['is_active'];

            $pathCategories = explode('/', $category['path']);

            $path = array();
            //don't need root category
            array_shift($pathCategories);
            foreach ($pathCategories as $pathCategoryId) {
                // do not include skippable categories in list
                if (in_array($pathCategoryId, $skippableCategories)) {
                    unset($data['categories'][$categoryId]);
                    continue 2;
                }

                if (isset($this->_categories[$storeId]) && isset($this->_categories[$storeId][$pathCategoryId])) {
                    $cat = $this->_categories[$storeId][$pathCategoryId];
                    if ($cat['level'] > 1) {
                        $pathPart = $cat['name'];
                        $pathPart = str_replace('/','\/', $pathPart);
                        $path[] = $pathPart;
                    }
                }
            }
            $data['categories'][$categoryId]['path'] = implode('/', $path);

            // and also add the anchor categories if needed
            $anchorCategories = $category['anchorsAbove'];
            foreach ($anchorCategories as $anchorCategoryId) {
                if (isset($data['categories'][$anchorCategoryId])
                        || !isset($this->_categories[$storeId][$anchorCategoryId])) {
                    continue;
                }

                $anchorCategory = $this->_categories[$storeId][$anchorCategoryId];
                $data['categories'][$anchorCategoryId]['name'] = $anchorCategory['name'];

                $anchorPathCategories = explode('/', $anchorCategory['path']);
                $anchorPath = [];
                array_shift($anchorPathCategories);
                foreach ($anchorPathCategories as $anchorPathCategoryId) {
                    // do not include skippable categories in list
                    if (in_array($anchorPathCategoryId, $skippableCategories)) {
                        unset($data['categories'][$anchorCategoryId]);
                        continue 2;
                    }

                    if (isset($this->_categories[$storeId])
                            && isset($this->_categories[$storeId][$anchorPathCategoryId])) {
                        $cat = $this->_categories[$storeId][$anchorPathCategoryId];
                        if ($cat['level'] > 1) {
                            $pathPart = $cat['name'];
                            $pathPart = str_replace('/','\/', $pathPart);
                            $anchorPath[] = $pathPart;
                        }
                    }
                }

                //
                // set path as empty string because searchperience already knows the complete path
                // and only needs to know the id in addition
                //
                $data['categories'][$anchorCategoryId]['path'] = '';
            }
        }

        return $data;
    }

    /**
     * Get price information for product
     *
     * @param Mage_Catalog_Model_Product $product Product Model
     * @param array                      $data    Data
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
                if ($value) {
                    $data['productData'][$attributeCode] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Get product image information
     *
     * @param Mage_Catalog_Model_Product $product Product Model
     * @param array                      $data    Data
     * @return array
     */
    protected function _getProductImageInformation($product, $data)
    {
        $width = [
            'default' => Mage::getStoreConfig('searchperience/searchperience/listViewImageWidth'),
            'non_retina_small' => Mage::getStoreConfig('searchperience/searchperience/listViewNonRetinaSmallWidth'),
            'retina_small' => Mage::getStoreConfig('searchperience/searchperience/listViewRetinaSmallWidth'),
            'non_retina_large' => Mage::getStoreConfig('searchperience/searchperience/listViewNonRetinaLargeWidth'),
            'retina_large' => Mage::getStoreConfig('searchperience/searchperience/listViewRetinaLargeWidth'),
        ];

        $height = [
            'default' => Mage::getStoreConfig('searchperience/searchperience/listViewImageHeight'),
            'non_retina_small' => Mage::getStoreConfig('searchperience/searchperience/listViewNonRetinaSmallHeight'),
            'retina_small' => Mage::getStoreConfig('searchperience/searchperience/listViewRetinaSmallHeight'),
            'non_retina_large' => Mage::getStoreConfig('searchperience/searchperience/listViewNonRetinaLargeHeight'),
            'retina_large' => Mage::getStoreConfig('searchperience/searchperience/listViewRetinaLargeHeight'),
        ];

        if (empty($width['default']) || empty($height['default'])) {
            return $data;
        }

        // Get Media Attribute Codes
        $imageAttributes = ['image', 'small_image', 'thumbnail'];

        $imageHelper = Mage::helper('catalog/image'); /* @var Mage_Catalog_Helper_Image $imageHelper */

        foreach ($imageAttributes as $attributeCode) {
            try {
                $data['productData']['images'][$attributeCode] = $imageHelper->init($product, $attributeCode)->resize($width['default'], $height['default'])->__toString();
            } catch (Exception $e) {
                // Mage::logException($e);
                if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                    Mage::log(sprintf('Error while resizing "%s" image: %s', $attributeCode, $e->getMessage()), Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
                }
            }
            if ($attributeCode === 'image' && isset($data['productData']['images']['image'])) {
                // Prepare required other Images from Large Image to get best quality
                $type = 'non_retina_small';
                $data['productData']['images'][$type] = $imageHelper->init($product, $attributeCode)->resize($width[$type], $height[$type])->__toString();
                $type = 'retina_small';
                $data['productData']['images'][$type] = $imageHelper->init($product, $attributeCode)->resize($width[$type], $height[$type])->__toString();
                $type = 'non_retina_large';
                $data['productData']['images'][$type] = $imageHelper->init($product, $attributeCode)->resize($width[$type], $height[$type])->__toString();
                $type = 'retina_large';
                $data['productData']['images'][$type] = $imageHelper->init($product, $attributeCode)->resize($width[$type], $height[$type])->__toString();
            }
        }
        return $data;
    }

    /**
     * Checks, if given attribute shall be skipped for indexing
     *
     * @param string $attributeCode Attribute Code
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

    /**
     * Get document info
     *
     * @param mixed $productId Product Id
     * @param int   $storeId   Store Id
     * @return array
     */
    public function getDocumentInfo($productId, $storeId)
    {
        /** @var Aoe_Searchperience_Model_Client_Searchperience $client */
        $client = $this->_client;
        $document = $client->getDocumentRepository()->getByForeignId(Mage::helper('aoe_searchperience')->getProductUniqueId($productId, $storeId));
        return array(
            'LastProcessing' => $document->getLastProcessing(),
            'BoostFactor' => $document->getBoostFactor(),
            'Id' => $document->getId(),
            'IsMarkedForProcessing' => $document->getIsMarkedForProcessing(),
            'IsMarkedForDeletion' => $document->getIsMarkedForDeletion(),
            'IsProminent' => $document->getIsProminent(),
            'NoIndex' => $document->getNoIndex(),
            'Content' => $document->getContent(),
            'ForeignId' => $document->getForeignId(),
            'GeneralPriority' => $document->getGeneralPriority(),
            'MimeType' => $document->getMimeType(),
            'Source' => $document->getSource(),
            'TemporaryPriority' => $document->getTemporaryPriority(),
            'Url' => $document->getUrl(),
        );
    }

    /**
     * Check if Attribute is of Type Boolean (aka Yes/No Select)
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute Attribute
     * @return bool
     */
    protected function attributeIsBoolean($attribute)
    {
        if ($attribute->getBackendType() ==='int' &&
            ($attribute->getFrontendInput() === 'boolean' ||
                $attribute->getFrontendInput() === 'select') &&
            ($attribute->getData('source_model') === 'eav/entity_attribute_source_boolean' ||
                $attribute->getData('source_model') === 'catalog/product_status')) {
            return true;
        }
        return false;
    }
}
