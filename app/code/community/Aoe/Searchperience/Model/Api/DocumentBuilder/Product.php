<?php

class Aoe_Searchperience_Model_Api_DocumentBuilder_Product extends Aoe_Searchperience_Model_Api_DocumentBuilder_Abstract {
	protected $_attributes = NULL;

	protected function getStaticAttributeCodes() {
		return array('sku');

	
	protected function getEavAttributeCodes() {
		return array();
	}

	/**
	 * @param $productId
	 * @param $storeId
	 * @param $command
	 * @return Aoe_Searchperience_Model_Api_Document
	 */
	public function buildDocument($productId, $storeId, $command) {
		$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
		$document = new Aoe_Searchperience_Model_Api_Document();
		$document->setEntityType(self::ENTITY_PRODUCT);
		$document->setId($productId);
		$document->setSku($product->getSku());
		$document->setUrl($product->getUrlPath());
		$document->setStoreId($storeId);

		$document->setCommand($command);

		return $document;
	}
}