<?php

class Aoe_Searchperience_Model_Api_Document extends Apache_Solr_Document {

    private $_data = array();

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