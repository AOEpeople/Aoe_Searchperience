<?php

class Aoe_Searchperience_Model_Api_DocumentBuilder {

	/**
	 * @param $productId
	 * @param $storeId
	 * @param $command
	 * @return Aoe_Searchperience_Model_Api_Document
	 */
	public function buildDocumentForProduct($productId, $storeId, $command) {
		$documentBuilder = Mage::getModel('aoe_searchperience/api_documentBuilder_product');
		$document = $documentBuilder->buildDocument($productId, $storeId, $command);
		return $document;
	}
}