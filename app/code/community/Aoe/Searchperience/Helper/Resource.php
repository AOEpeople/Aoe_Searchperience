<?php
/**
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoe.com>
 */

class Aoe_Searchperience_Helper_Resource extends Mage_Core_Helper_Abstract
{
    /**
     * Close all db connections
     */
    public function closeAllDbConnections()
    {
        $this->_closeCoreConnections();
        $this->_closeEnterpriseConnections();
    }

    /**
     * Ugly hack because 'core/resource' model can't be rewritten via magento config xml
     * (singleton is created first time in App.php:269, Mage_Core_Model_App->init(),
     * when modules xml files aren't loaded yet)
     */
    protected function _closeCoreConnections()
    {
        $coreResource = Mage::getSingleton('core/resource');
        $reflectionObject = new ReflectionObject($coreResource);
        $reflectionProperty = $reflectionObject->getProperty('_connections');
        $reflectionProperty->setAccessible(true);
        $connections = $reflectionProperty->getValue($coreResource);

        /* @var $connection Magento_Db_Adapter_Pdo_Mysql */
        foreach ($connections as $connection) {
            $connection->closeConnection();
        }
    }

    protected function _closeEnterpriseConnections()
    {
        Mage::getSingleton('enterprise_index/resource_lock_resource')->closeConnections();

        try {
            Enterprise_Index_Model_Lock::getInstance()->shutdownReleaseLocks();
        } catch (Exception $e) {
            // we don't care
        }
    }
}
