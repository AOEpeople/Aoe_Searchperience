<?php

class Aoe_Searchperience_Model_Api_Document extends Mage_Core_Model_Abstract {

	const XML_DOCUMENT_TEMPLATE = "<?xml version='1.0'>
	<document>
		<command></command>
		<foreignId></foreignId>
		<content></content>
	</document>";

	/**
	 * @return string|void
	 */
	public function toXml() {
		$xml = new SimpleXMLElement(self::XML_DOCUMENT_TEMPLATE);
		$xml->document->command = $this->getCommand();
		$xml->document->foreignId = $this->getForeignId();
		return $xml->asXML();
	}

	protected function getForeignId() {
		return $this->getEntityType() . '_' . $this->getId();
	}
}