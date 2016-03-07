<?php

require_once 'abstract.php';

/**
 * Aoe Searchperience shell script
 *
 * @author Fabrizio Branca
 * @since 2013-11-06
 */
class Aoe_Searchperience_Shell_Searchperience extends Mage_Shell_Abstract
{

    public function indexProductsAction() {
        $productIds = $this->trimExplode(',', $this->getArg('productIds'), true);
        if (count($productIds) == 0) {
            $productIds = null;
        }

        $index = Mage::getSingleton('catalogsearch/fulltext'); /* @var $index Mage_CatalogSearch_Model_Fulltext */

        $storeIds = $this->getArg('storeIds');
        if (!empty($storeIds)) {
            $storeIds = $this->trimExplode(',', $storeIds, true);
            foreach ($storeIds as $storeId) {
                $index->rebuildIndex($storeId, $productIds);
            }
        } else {
            $index->rebuildIndex(null, $productIds);
        }
    }

    public function indexProductsActionHelp() {
        return ' [-productIds <csl of product ids, defaults to all products>] [-storeIds <csl of store ids, defaults to all stores>]';
    }



    public function deleteIndexActionHelp() {
        return '    [[CAUTION! This will flush all products from the Searchperience index!]]';
    }

    public function deleteIndexAction() {
        $source = Mage::getStoreConfig('searchperience/searchperience/source');
        echo "Deprecated! Please run following command instead:\n";
        echo " -action deleteBySource -source $source\n";
        exit(1);
    }



    public function deleteBySourceAction() {
        if (!Mage::helper('aoe_searchperience')->isDeletionEnabled()) {
            echo "Document deletion disabled in configuration!\n";
            exit(1);
        }

        $source = $this->getArg('source');

        $api = Mage::getSingleton('aoe_searchperience/searchperienceApi'); /* @var $api Aoe_Searchperience_Model_SearchperienceApi */
        $api->deleteBySource($source);

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            echo "Check log file " . Aoe_Searchperience_Helper_Data::LOGFILE . " for more information\n";
        } else {
            echo "Logging disabled. We don't know if that request was successful or not...\n";
        }
    }

    public function deleteBySourceActionHelp() {
        return " -source <source> (e.g. magento_product - see Mage::getStoreConfig('searchperience/searchperience/source')";
    }

    public function deleteByForeignIdAction() {
        if (!Mage::helper('aoe_searchperience')->isDeletionEnabled()) {
            echo "Document deletion disabled in configuration!\n";
            exit(1);
        }

        $foreignIds = $this->getArg('foreignIds');
        $foreignIds = $this->trimExplode(',', $foreignIds, true);

        $api = Mage::getSingleton('aoe_searchperience/searchperienceApi'); /* @var $api Aoe_Searchperience_Model_SearchperienceApi */
        $api->deleteById($foreignIds);

        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            echo "Check log file " . Aoe_Searchperience_Helper_Data::LOGFILE . " for more information\n";
        } else {
            echo "Logging disabled. We don't know if that request was successful or not...\n";
        }
    }

    public function deleteByForeignIdActionHelp() {
        return " -foreignIds <csl of foreign ids>";
    }


    public function getProductInfoAction() {

        $productId = $this->getArg('productId');
        if (empty($productId)) {
            echo "No productId given\n";
            exit(1);
        }

        $storeId = $this->getArg('storeId');
        if (empty($storeId)) {
            echo "No storeId given\n";
            exit(1);
        }

        $adapter = Mage::getSingleton('aoe_searchperience/searchperienceApi'); /* @var $adapter Aoe_Searchperience_Model_SearchperienceApi */
        $info = $adapter->getDocumentInfo($productId, $storeId);

        var_dump($info);
    }

    public function getProductInfoActionHelp() {
        return ' -productId <product ids> -storeId <store id>';
    }

    /** ****************************************************************************************************************
     * SHELL DISPATCHER
     **************************************************************************************************************** */

    /**
     * Run script
     *
     * @return void
     */
    public function run() {
        $action = $this->getArg('action');
        if (empty($action)) {
            echo $this->usageHelp();
        } else {
            $actionMethodName = $action.'Action';
            if (method_exists($this, $actionMethodName)) {
                $this->$actionMethodName();
            } else {
                echo "Action $action not found!\n";
                echo $this->usageHelp();
                exit(1);
            }
        }
    }



    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp() {
        $help = 'Available actions: ' . "\n";
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, -6) == 'Action') {
                $help .= '    -action ' . substr($method, 0, -6);
                $helpMethod = $method.'Help';
                if (method_exists($this, $helpMethod)) {
                    $help .= $this->$helpMethod();
                }
                $help .= "\n";
            }
        }
        return $help;
    }


    /**
     * trim explode
     *
     * @param $delim
     * @param $string
     * @param bool $removeEmptyValues
     * @return array
     */
    public function trimExplode($delim, $string, $removeEmptyValues = false)
    {
        $explodedValues = explode($delim, $string);
        $result = array_map('trim', $explodedValues);
        if ($removeEmptyValues) {
            $temp = array();
            foreach ($result as $value) {
                if ($value !== '') {
                    $temp[] = $value;
                }
            }
            $result = $temp;
        }
        return $result;
    }

}


$shell = new Aoe_Searchperience_Shell_Searchperience();
$shell->run();