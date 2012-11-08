<?php

class Aoe_Searchperience_Model_Api_Client extends Mage_Core_Model_Abstract {

	/**
	 * @param Aoe_Searchperience_Model_Api_Document $document
	 * @return int status code
	 */
	public function sendDocument($document) {
		$content = $document->toXml();
		//send content
		//get response
		return 200;
	}

}