<?php

class Aoe_Searchperience_Model_Adapter_Solr extends Enterprise_Search_Model_Adapter_Solr_Abstract
{
    /**
     * Object name used to create solr document object
     *
     * @var string
     */
    protected $_clientDocObjectName = 'Aoe_Searchperience_Model_Api_Document';

    /**
     * Connect to Search Engine Client by specified options.
     * Should initialize _client
     *
     * @param array $options
     */
    protected function _connect($options = array())
    {
        // TODO: Implement _connect() method.
    }

    /**
     * Simple Search interface
     *
     * @param string $query
     * @param array $params
     */
    protected function _search($query, $params = array())
    {
        // TODO: Implement _search() method.
    }
}
