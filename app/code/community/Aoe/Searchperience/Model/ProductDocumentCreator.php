<?php
/**
 * Product Document Creator
 *
 * @author Fabrizio Branca
 * @since 2015-06-17
 */
class Aoe_Searchperience_Model_ProductDocumentCreator {

    /**
     * Create document
     * (If this method returns false this document will be deleted from the index instead)
     *
     * @param $productId
     * @param $storeId
     * @return array|false
     */
    public function createDocument($productId, $storeId) {
        $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId); /* @var $product Mage_Catalog_Model_Product */

        // skip disabled products
        if ($product->isDisabled()) {
            return false;
        }

        // skip "invisible" products
        if (!in_array($product->getVisibility(), Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds())) {
            return false;
        }

        // dispatch event for other skip reasons
        $transport = new Varien_Object(array('product' => $product, 'store_id' => $storeId));
        Mage::dispatchEvent('aoe_searchperience_productdocumentcreator_createDocument_skip', array('transport' => $transport));
        if ($transport->getSkip()) {
            return false;
        }

        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString("\t");
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('product');

        $writer->writeElement('id', $product->getId());
        $writer->writeElement('storeid', $storeId);
        $writer->writeElement('language', Mage::getStoreConfig('general/locale/code', $storeId));
        $writer->writeElement('availability', $this->getAvailabilitySpeakingName($product));

        $documentFields = array(
            'sku'               => 'sku',
            'title'             => 'name',
            'description'       => 'description',
            'short_description' => 'short_description',
            'price'             => 'price',
            'special_price'     => 'special_price',
            'group_price'       => 'group_price',
            'rating'            => 'rating',
        );

        // add product data to xml
        foreach ($documentFields as $elementName => $productDataName) {
            $writer->startElement($elementName);
            $writer->writeCData($product->getDataUsingMethod($productDataName));
            $writer->endElement();
        }

        $writer->startElement('content');
        $writer->writeCData($this->getHtmlContent($product, $storeId));
        $writer->endElement();

        $categoryHelper = Mage::helper('aoe_searchperience/category'); /* @var $categoryHelper Aoe_Searchperience_Helper_Category */
        foreach ($product->getCategoryIds() as $categoryId) {
            $writer->writeElement('category_path', $categoryHelper->getPathForCategory($categoryId, $storeId));
            $writer->writeElement('category_id', $categoryId);
        }

        $writer->writeElement('image_link', $this->getImage($product));


        $attributes = $this->getAttributes($product, $storeId);

        foreach ($attributes as $attributeName => $attributeData) {
            $writer->startElement('attribute');
            $writer->writeAttribute('name', $attributeName);

            if (!isset($attributeData['xmlnode_attributes']) || !is_array($attributeData['xmlnode_attributes'])) {
                $attributeData['xmlnode_attributes'] = array();
            }
            if (!isset($attributeData['xmlnode_attributes']['type'])) {
                $attributeData['xmlnode_attributes']['type'] = 'string';
            }
            if (!in_array($attributeData['xmlnode_attributes']['type'], array('string', 'float', 'date'))) {
                Mage::throwException('Invalid type');
            }

            foreach ($attributeData['xmlnode_attributes'] as $key => $value) {
                if (!in_array($key, array('type', 'forsorting', 'forfaceting', 'forsearching'))) {
                    Mage::throwException('Invalid attribute');
                }
                $writer->writeAttribute($key, $value);
            }

            $value = $attributeData['value'];
            if (!is_array($value)) {
                $value = (array)$value;
            }
            $value = array_unique($value);
            foreach ($value as $key => $attributeValue) {
                if ($attributeValue) {
                    $writer->writeElement('value', $attributeValue);
                }
            }

            $writer->endElement();
        }


        // end product node
        $writer->endElement();
        $writer->endDocument();

        $rawDocument = $writer->outputMemory(true);

        return array(
            'raw_document' => $rawDocument,
            'url' => $product->getProductUrl()
        );
    }

    /**
     * Get image
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getImage(Mage_Catalog_Model_Product $product)
    {
        $image = '';
        $attributeCode = Mage::getStoreConfig('searchperience/searchperience/listViewImageType');

        $width = Mage::getStoreConfig('searchperience/searchperience/listViewImageWidth');
        $height = Mage::getStoreConfig('searchperience/searchperience/listViewImageHeight');

        if (empty($width) || empty($height)) {
            return $image;
        }

        try {
            $imageHelper = Mage::helper('catalog/image'); /* @var $imageHelper Mage_Catalog_Helper_Image */
            $image = $imageHelper->init($product, $attributeCode)->resize($width, $height)->__toString();
        } catch (Exception $e) {
            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                Mage::log(sprintf('Error while resizing "%s" image: %s', $attributeCode, $e->getMessage()), Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
            }
        }

        return $image;
    }

    /**
     * Get availability
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getAvailabilitySpeakingName(Mage_Catalog_Model_Product $product)
    {
        $availability = $product->getIsInStock();
        switch(filter_var($availability, FILTER_VALIDATE_INT)) {
            case 0: return 'out_of_stock';
            case 1: return 'in_stock';
            default: return $availability;
        }
    }

    /**
     * Get HTML content
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getHtmlContent(Mage_Catalog_Model_Product $product, $storeId) {

        $htmlContent = '';

        $transport = new Varien_Object();
        $transport->setHtmlContent($htmlContent);
        $transport->setProduct($product);
        $transport->setStoreId($storeId);
        Mage::dispatchEvent('aoe_searchperience_productdocumentcreator_gethtmlcontent', array('transport' => $transport));
        $htmlContent = $transport->getHtmlContent();

        return $htmlContent;
    }

    /**
     * Get attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function getAttributes(Mage_Catalog_Model_Product $product, $storeId) {

        $attributes = array();

        /**
         * Expected format
         * array('attributeName' => array(
         *      'xmlnode_attributes' => array(
         *          'type' => 'string'
         *          'forfaceting' => 1
         *      ),
         *      'value' => array(
         *          'value1',
         *          'value2'
         *      )
         * ))
         */

        $transport = new Varien_Object();
        $transport->setAttributes($attributes);
        $transport->setProduct($product);
        $transport->setStoreId($storeId);
        Mage::dispatchEvent('aoe_searchperience_productdocumentcreator_getattributes', array('transport' => $transport));
        $attributes = $transport->getAttributes();

        return $attributes;
    }

}