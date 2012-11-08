<?php

class Aoe_Searchperience_Model_Api_DocumentBuilder_Abstract {
	const ENTITY_PRODUCT = 'product';
	const ENTITY_CATEGORY = 'category';
	const ENTITY_PAGE = 'cms_page';

	protected function getStaticAttributeCodes() {
		return array();
	}

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
		$document = new Aoe_Searchperience_Model_Api_Document();
		return $document;
	}
}