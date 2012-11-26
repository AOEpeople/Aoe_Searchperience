<?php

class Aoe_Searchperience_Model_Adapter_Searchperience extends Enterprise_Search_Model_Adapter_Solr_Abstract
{
    /**
     * Object name used to create solr document object
     *
     * @var string
     */
    protected $_clientDocObjectName = 'Aoe_Searchperience_Model_Api_Document';

    /**
     * Holds searchable product attributes
     *
     * @var array
     */
    protected  $_searchableProductAttributes = array();

    /**
     * Holds data for indexing product
     *
     * @var array
     */
    protected $_indexData = array();

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

        $docs = array();
        foreach ($docData as $productId => $productIndexData) {
            $doc = new $this->_clientDocObjectName;
            $productIndexData = $this->_prepareIndexProductData($productIndexData, $productId, $storeId);
            if (!$productIndexData) {
                continue;
            }

            $doc->setData($productIndexData);
            $docs[] = $doc;
        }

        return $docs;
    }

    /**
     * Prepare index data for using in search engine metadata.
     * Prepare fields for advanced search, navigation, sorting and fulltext fields for each search weight for
     * quick search and spell.
     *
     * @param array $productIndexData
     * @param int $productId
     * @param int $storeId
     * @deprecated
     *
     * @return  array|bool
     */
    protected function _prepareIndexProductData($productIndexData, $productId, $storeId)
    {
        if (!$this->isAvailableInIndex($productIndexData, $productId)) {
            return false;
        }

        // fetch searchable product attributes
        $this->_getSearchableAttributes();

        $productIds        = array();
        $this->_indexData  = array(
            'storeid'  => $storeId,
            'language' => Mage::getStoreConfig('general/locale/code', $storeId),
            'in_stock' => (!empty($productIndexData['in_stock']) ? $productIndexData['in_stock'] : ''),
        );

        $product = Mage::getModel('catalog/product')->load($productId);

        $this->_indexData['productData']['id']     = $product->getId();
        $this->_indexData['productData']['sku']    = $product->getSku();
        $this->_indexData['productData']['url']    = $product->getProductUrl();
        $this->_indexData['productData']['unique'] = $product->getId() . '_' . $storeId;

        // fetch price information
        $this->_getProductPriceInformation($product);

        // fetch image information
        $this->_getProductImageInformation($product);

        // fetch category information
        foreach ($product->getCategoryIds() as $categoryId) {
            if (!isset($this->_indexData['categories'][$categoryId])) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $this->_indexData['categories'][$categoryId]['name'] = $category->getName();

                $pathCategories = explode('/', $category->getPath());
                $path = array();

                foreach($pathCategories as $pathCategory) {
                    $cat = Mage::getModel('catalog/category')->load($pathCategory);
                    if ($cat->getLevel() > 1) {
                        $pathPart = $cat->getName();
                        $pathPart = str_replace('/','&#47;', $pathPart);
                        $path[] = $pathPart;
                    }
                }
                $this->_indexData['categories'][$categoryId]['path'] = implode('/', $path);
            }
        }

//        // fetch related products
//        foreach ($product->getRelatedProducts() as $relatedProduct) {
//            $this->_indexData['productData']['related'][] = $relatedProduct->getId();
//        }
//
//        // fetch upsell products
//        foreach ($product->getUpSellProducts() as $upsellProduct) {
//            $this->_indexData['productData']['upsell'][] = $upsellProduct->getId();
//        }
//
//        // fetch crosssell products
//        foreach ($product->getCrossSellProducts() as $crossProduct) {
//            $this->_indexData['productData']['cross'][] = $crossProduct->getId();
//        }

//        foreach ($productIndexData as $attributeCode => $value) {
//            if ($this->_skipAttribute($attributeCode)) {
//                continue;
//            }
//
//            if (is_array($value)) {
//                $this->_indexData['productData'][$attributeCode] = $value[$productId];
//            }
//            else {
//                $this->_indexData[$attributeCode] = $value;
//            }
//        }

        // fetch additional product information
        $this->_getAdditionalProductData($productIndexData, $productId, $storeId);

        return $this->_indexData;
    }

    /**
     * Get additional product data
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _getAdditionalProductData($productIndexData, $productId, $storeId)
    {
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
            if (!in_array($attributeCode, $this->_usedFields)) {
             //   unset($productIndexData[$attributeCode]);
            }

            if (!$attribute || $attributeCode == 'price' || empty($attributeValue)) {
                continue;
            }


            $attribute->setStoreId($storeId);

            // Preparing data for solr fields
            if ($attribute->getIsSearchable() || $attribute->getIsVisibleInAdvancedSearch()
                || $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()
            ) {
                $backendType = $attribute->getBackendType();
                $frontendInput = $attribute->getFrontendInput();

                if ($attribute->usesSource()) {
                    if ($frontendInput == 'multiselect') {
                        $preparedValue = array();
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
                    $preparedValue = $attributeValue;
                    if ($backendType == 'datetime') {
                        if (is_array($attributeValue)) {
                            $preparedValue = array();
                            foreach ($attributeValue as &$val) {
                                $val = $this->_getSolrDate($storeId, $val);
                                if (!empty($val)) {
                                    $preparedValue[] = $val;
                                }
                            }
                            unset($val); //clear link to value
                            $preparedValue = array_unique($preparedValue);
                        } else {
                            $preparedValue = $this->_getSolrDate($storeId, $attributeValue);
                        }
                    }
                }

            }

//            // Preparing data for sorting field
//            if ($attribute->getUsedForSortBy()) {
//                if (is_array($preparedValue)) {
//                    if (isset($preparedValue[$productId])) {
//                        $sortValue = $preparedValue[$productId];
//                    } else {
//                        $sortValue = null;
//                    }
//                }
//
//                if (!empty($sortValue)) {
//                    $fieldName = $this->getSearchEngineFieldName($attribute, 'sort');
//
//                    if ($fieldName) {
//                        $productIndexData[$fieldName] = $sortValue;
//                    }
//                }
//            }
            if ($attribute->getUsedForSortBy()) {
                $usedForSorting[] = $attributeCode;
            }

            if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
                $usedForFiltering[] = $attributeCode;
            }
			$attributeTypes[$attributeCode] = $this->getAttributeSearchType($attribute);
            $productIndexData[$attributeCode] = empty($preparedValue[$productId]) && !empty($preparedNavValue[$productId]) ? $preparedNavValue[$productId] : $preparedValue[$productId];

            // Adding data for advanced search field (without additional prefix)
//            if (($attribute->getIsVisibleInAdvancedSearch() ||  $attribute->getIsFilterable()
//                || $attribute->getIsFilterableInSearch())
//            ) {
//                if ($attribute->usesSource()) {
//                    $fieldName = $this->getSearchEngineFieldName($attribute, 'nav');
//                    if ($fieldName && !empty($preparedNavValue)) {
//                        $productIndexData[$fieldName] = $preparedNavValue;
//                    }
//                } else {
//                    $fieldName = $this->getSearchEngineFieldName($attribute);
//                    if ($fieldName && !empty($preparedValue)) {
//                        $productIndexData[$fieldName] = in_array($backendType, $this->_textFieldTypes)
//                            ? implode(' ', (array)$preparedValue)
//                            : $preparedValue ;
//                    }
//                }
//            }

//            // Adding data for fulltext search field
//            if ($attribute->getIsSearchable() && !empty($preparedValue)) {
//                $searchWeight = $attribute->getSearchWeight();
//                if ($searchWeight) {
//                    $fulltextData[$searchWeight][] = is_array($preparedValue)
//                        ? implode(' ', $preparedValue)
//                        : $preparedValue;
//                }
//            }

            unset($preparedNavValue, $preparedValue, $fieldName, $attribute);
        }

        Mage::log(var_export($productIndexData, true));

        //return $productIndexData;
    }

	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute|string $attribute
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
		} elseif ($backendType == 'datetime') {
			$fieldType = 'date';
		} else {
			$fieldType = 'text';
		}

		return $fieldType;
	}

    /**
     * Retrieve attribute solr field name
     *
     * @param   Mage_Catalog_Model_Resource_Eav_Attribute|string $attribute
     * @param   string $target - default|sort|nav
     *
     * @return  string|bool
     */
    public function getSearchEngineFieldName($attribute, $target = 'default')
    {
        if (is_string($attribute)) {
            if ($attribute == 'price') {
                return $this->getPriceFieldName();
            }

            $eavConfig  = Mage::getSingleton('eav/config');
            $entityType = $eavConfig->getEntityType('catalog_product');
            $attribute  = $eavConfig->getAttribute($entityType, $attribute);
        }

        // Field type defining
        $attributeCode = $attribute->getAttributeCode();
        if (in_array($attributeCode, array('sku'))) {
            return $attributeCode;
        }

        if ($attributeCode == 'price') {
            return $this->getPriceFieldName();
        }

        $backendType    = $attribute->getBackendType();
        $frontendInput  = $attribute->getFrontendInput();

        if ($frontendInput == 'multiselect') {
            $fieldType = 'multi';
        } elseif ($frontendInput == 'select' || $frontendInput == 'boolean') {
            $fieldType = 'select';
        } elseif ($backendType == 'decimal' || $backendType == 'datetime') {
            $fieldType = $backendType;
        } else {
            $fieldType = 'text';
        }

        // Field prefix construction. Depends on field usage purpose - default, sort, navigation
        $fieldPrefix = 'attr_';
        if ($target == 'sort') {
            $fieldPrefix .= $target . '_';
        } elseif ($target == 'nav') {
            if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch() || $attribute->usesSource()) {
                $fieldPrefix .= $target . '_';
            }
        }

        if ($fieldType == 'text') {
            $localeCode     = Mage::app()->getStore($attribute->getStoreId())
                ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
            $languageSuffix = Mage::helper('enterprise_search')->getLanguageSuffix($localeCode);
            $fieldName      = $fieldPrefix . $attributeCode . $languageSuffix;
        } else {
            $fieldName      = $fieldPrefix . $fieldType . '_' . $attributeCode;
        }

        return $fieldName;
    }

    /**
     * Get additional product data
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function __getAdditionalProductData($product)
    {
        $productData = $product->getData();

        foreach ($this->_searchableProductAttributes as $attributeCode => $attribute) {
            if (!isset($this->_indexData['productData'][$attributeCode]) && !isset($this->_indexData[$attributeCode])) {
                if (isset($productData[$attributeCode])) {
                    $this->_indexData['productData']['additionalData'][$attributeCode] = $productData[$attributeCode];
                }
            }
        }

        $this->_indexData['productData']['additionalData']['manufacturer'] = $product->getAttributeText('manufacturer');
        $this->_indexData['productData']['additionalData']['color'] = $product->getAttributeText('color');

        if ($product->isConfigurable()) {
            foreach ($product->getTypeInstance()->getConfigurableAttributes() as $attribute) {
                Mage::log($attribute->getLabel());
            }
        }
    }

    /**
     * Get price information for product
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _getProductPriceInformation($product)
    {
        // define attributes and get methods
        $attributes = array(
            'special_price' => 'getSpecialPrice',
            // @TODO: make group prices work!
            'group_price'   => 'getGroupPrice',
        );

        foreach ($attributes as $attributeCode => $getMethod) {
            if (!empty($this->_searchableProductAttributes[$attributeCode])) {
                $this->_indexData['productData'][$attributeCode] = $product->$getMethod();
            }
        }
    }

    /**
     * Get product image information
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _getProductImageInformation($product)
    {
        $width  = 201;
        $height = 201;

        // define attributes and get methods
        $attributes = array(
            'thumbnail'   => 'getThumbnailUrl',
            'image'       => 'getImageUrl',
            'small_image' => 'getSmallImageUrl',
        );

        foreach ($attributes as $attributeCode => $getMethod) {
            $this->_indexData['productData']['images'][$attributeCode] = $product->$getMethod($width, $height);
        }
    }

    /**
     * Determines searchable product attributes
     */
    protected function _getSearchableAttributes()
    {
        if (empty($this->_searchableProductAttributes)) {
            $productAttributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
            $productAttributeCollection->addSearchableAttributeFilter();

            foreach ($productAttributeCollection->getItems() as $attribute) {
                $this->_searchableProductAttributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
    }

    /**
     * Checks, if given attribute shall be skipped for indexing
     *
     * @param $attributeCode
     */
    protected function _skipAttribute($attributeCode)
    {
        // not in the list of searchable attributes
        if (!in_array($attributeCode, array_keys($this->_searchableProductAttributes))) {
            return true;
        }

        // is user defined
        if (
            isset($this->_searchableProductAttributes[$attributeCode]) &&
            1 == $this->_searchableProductAttributes[$attributeCode]->isUserDefined
        ) {
            return true;
        }

        return false;
    }

}
