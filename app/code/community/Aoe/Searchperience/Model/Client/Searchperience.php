<?php

class Aoe_Searchperience_Model_Client_Searchperience {
//extends Apache_Solr_Service
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

	public function ping() {
		return true;
	}

	public function commit(){

	}

	public function addDocuments() {

	}
}