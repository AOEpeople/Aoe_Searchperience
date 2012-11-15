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
		return true;
	}
}