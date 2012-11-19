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

        if (!$this->isAvailableInIndex($productIndexData, $productId)) {
            return false;
        }

        // fetch searchable product attributes
        $this->_getSearchableAttributes();

        $productIds        = array();
        $this->_indexData  = array(
            'storeid'  => $storeId,
            'language' => Mage::getStoreConfig('general/locale/code', $storeId),
        );

        foreach ($productIndexData as $attributeCode => $value) {
            if (
                (!in_array($attributeCode, array_keys($this->_searchableProductAttributes))) ||
                (
                    isset($this->_searchableProductAttributes[$attributeCode]) &&
                    1 == $this->_searchableProductAttributes[$attributeCode]->isUserDefined
                )
            ) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $id => $attributeValue) {
                    $this->_indexData['products'][$id][$attributeCode] = $attributeValue;
                    $productIds[$id] = 1;
                }
            }
            else {
                $this->_indexData[$attributeCode] = $value;
            }
        }

        foreach (array_keys($productIds) as $pid) {
            $product = Mage::getModel('catalog/product')->load($pid);

            $this->_indexData['products'][$pid]['sku'] = $product->getSku();

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
                            $path[] = $cat->getName();
                        }
                    }
                    $this->_indexData['categories'][$categoryId]['path'] = implode(' > ', $path);
                }
            }

            // fetch related products
            foreach ($product->getRelatedProducts() as $relatedProduct) {
                $this->_indexData['products'][$pid]['related'][] = $relatedProduct->getId();
            }

            // fetch upsell products
            foreach ($product->getUpSellProducts() as $upsellProduct) {
                $this->_indexData['products'][$pid]['upsell'][] = $upsellProduct->getId();
            }

            // fetch crosssell products
            foreach ($product->getCrossSellProducts() as $crossProduct) {
                $this->_indexData['products'][$pid]['cross'][] = $crossProduct->getId();
            }

            // fetch additional product information
            $this->_getAdditionalProductData($product);

        }

        return $this->_indexData;
    }

    /**
     * Get price information for product
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _getProductPriceInformation($product) {

        // define attributes and get methods
        $attributes = array(
            'special_price' => 'getSpecialPrice',
            'group_price'   => 'getGroupPrice',
        );

        foreach ($attributes as $attributeCode => $getMethod) {
            if (!empty($this->_searchableProductAttributes[$attributeCode])) {
                $this->_indexData['products'][$product->getId()][$attributeCode] = $product->$getMethod();
            }
        }
    }

    /**
     * Get product image information
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _getProductImageInformation($product) {

        // define attributes and get methods
        $attributes = array(
            'thumbnail'   => 'getThumbnailUrl',
            'image'       => 'getImageUrl',
            'small_image' => 'getSmallImageUrl',
        );

        foreach ($attributes as $attributeCode => $getMethod) {
            if (!empty($this->_searchableProductAttributes[$attributeCode])) {
                $this->_indexData['products'][$product->getId()]['images'][$attributeCode] = $product->$getMethod();
            }
        }
    }

    /**
     * Get additional product data
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _getAdditionalProductData($product) {

        $productData = $product->getData();

        foreach ($this->_searchableProductAttributes as $attributeCode => $attribute) {
            if (!isset($this->_indexData['products'][$product->getId()][$attributeCode]) && !isset($this->_indexData[$attributeCode])) {
                if (isset($productData[$attributeCode])) {
                    $this->_indexData['products'][$product->getId()]['additionalData'][$attributeCode] = $productData[$attributeCode];
                }
            }
        }
    }

    /**
     * Determines searchable product attributes
     */
    protected function _getSearchableAttributes() {

        $productAttributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        $productAttributeCollection->addSearchableAttributeFilter();

        foreach ($productAttributeCollection->getItems() as $attribute) {
            $this->_searchableProductAttributes[$attribute->getAttributeCode()] = $attribute;
        }
    }
}
