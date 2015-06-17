<?php
/**
 * Product Document Creator
 *
 * @author Fabrizio Branca
 * @since 2015-06-17
 */
class Aoe_Searchperience_Model_ProductDocumentCreator {

    public function createDocument($productId, $storeId) {
        $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId); /* @var $product Mage_Catalog_Model_Product */

        $rawDocument = $product->getName();

        return array(
            'raw_document' => $rawDocument,
            'url' => $product->getProductUrl()
        );
    }

}