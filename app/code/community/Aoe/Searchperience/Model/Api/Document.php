<?php

class Aoe_Searchperience_Model_Api_Document extends Apache_Solr_Document {

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