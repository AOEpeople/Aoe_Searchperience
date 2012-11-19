<?php

class Aoe_Searchperience_Model_Api_Document extends Apache_Solr_Document {

	const XML_DOCUMENT_TEMPLATE = "<?xml version='1.0'>
	<document>
		<command></command>
		<foreignId></foreignId>
		<content></content>
	</document>";

    private $_data = array();

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

    /**
     * Stores data internally
     *
     * @param array $data
     */
    public function setData($data = array())
    {
        $this->_data = $data;
    }

    /**
     * Returns internal stored data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}