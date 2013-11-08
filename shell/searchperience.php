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
            echo "No productIds given\n";
            exit(1);
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
        return ' -productIds <csl of product ids> [-storeIds <csl of store ids, defaults to all stores>]';
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