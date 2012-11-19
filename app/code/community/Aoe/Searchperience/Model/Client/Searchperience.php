<?php

class Aoe_Searchperience_Model_Client_Searchperience extends Apache_Solr_Service

{
	/**
	 * @param array $options
	 */
	public  function __construct($options) {
		return $this;
	}

    /**
     * Create a delete document based on a multiple queries and submit it
     *
     * @param array $rawQueries Expected to be utf-8 encoded
     * @param boolean $fromPending
     * @param boolean $fromCommitted
     * @param float $timeout Maximum expected duration of the delete operation on the server (otherwise, will throw a communication exception)
     * @return Apache_Solr_Response
     *
     * @throws Exception If an error occurs during the service call
     */
    public function deleteByQueries($rawQueries, $fromPending = true, $fromCommitted = true, $timeout = 3600)
    {
        return true;
    }

	/*
	* @param float $timeout maximum time to wait for ping in seconds, -1 for unlimited (default is 2)
	* @return float Actual time taken to ping the server, FALSE if timeout or HTTP error status occurs
	*/
	public function ping($timeout = 2) {
		return 0.1;
	}

	/**
	 * Send a commit command.  Will be synchronous unless both wait parameters are set to false.
	 *
	 * @param boolean $optimize Defaults to true
	 * @param boolean $waitFlush Defaults to true
	 * @param boolean $waitSearcher Defaults to true
	 * @param float $timeout Maximum expected duration (in seconds) of the commit operation on the server (otherwise, will throw a communication exception). Defaults to 1 hour
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function commit($optimize = true, $waitFlush = true, $waitSearcher = true, $timeout = 3600)
	{
		return true;
	}

	/**
	 * Add an array of Solr Documents to the index all at once
	 *
	 * @param array $documents Should be an array of Apache_Solr_Document instances
	 * @param boolean $allowDups
	 * @param boolean $overwritePending
	 * @param boolean $overwriteCommitted
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If an error occurs during the service call
	 */
	public function addDocuments($documents, $allowDups = false, $overwritePending = true, $overwriteCommitted = true)
	{
//		foreach ($documents as $document) {
//            Mage::log(__CLASS__ . ':' . __LINE__ . ' - ' . $this->_documentToXmlFragment($document));
//        }
	}

    /**
     * Create an XML fragment from a {@link Apache_Solr_Document} instance appropriate for use inside a Solr add call
     *
     * @return string
     */
    protected function _documentToXmlFragment(Apache_Solr_Document $document)
    {
        $xml = '<product xmlns="urn:com.searchperience.indexing.product">';

        foreach ($document as $key => $value)
        {
            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            $fieldBoost = $document->getFieldBoost($key);

            if (is_array($value))
            {
                foreach ($value as $multivalue)
                {
                    $xml .= '<field name="' . $key . '"';

                    if ($fieldBoost !== false)
                    {
                        $xml .= ' boost="' . $fieldBoost . '"';

                        // only set the boost for the first field in the set
                        $fieldBoost = false;
                    }

                    $multivalue = htmlspecialchars($multivalue, ENT_NOQUOTES, 'UTF-8');

                    $xml .= '>' . $multivalue . '</field>';
                }
            }
            else
            {
                $xml .= '<field name="' . $key . '"';

                if ($fieldBoost !== false)
                {
                    $xml .= ' boost="' . $fieldBoost . '"';
                }

                $value = htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');

                $xml .= '>' . $value . '</field>';
            }
        }

        $xml .= '</product>';

        // replace any control characters to avoid Solr XML parser exception
        return $this->_stripCtrlChars($xml);
    }
}