<?php

/**
 * Class Aoe_Searchperience_Model_Resource_Lock_Resource
 *
 * @author Fabrizio Branca
 * @since 2013-11-25
 */
class Aoe_Searchperience_Model_Resource_Lock_Resource extends Enterprise_Index_Model_Resource_Lock_Resource {

    /**
     * Close all connections
     */
    public function closeConnections() {
        foreach ($this->_connections as $connection) { /* @var $connection Magento_Db_Adapter_Pdo_Mysql */
            $connection->closeConnection();
        }
        $this->_connections = array();
    }

}