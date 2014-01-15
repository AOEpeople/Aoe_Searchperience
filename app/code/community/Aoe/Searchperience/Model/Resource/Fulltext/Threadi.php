<?php

require_once 'Threadi/Loader.php';

class Aoe_Searchperience_Model_Resource_Fulltext_Threadi extends Aoe_Searchperience_Model_Resource_Fulltext
{

    /**
     * @var Threadi_Pool
     */
    protected $threadPool;

    /**
     * @var int thread counter
     */
    protected $threadCounter = 0;

    /**
     * @var bool
     */
    protected $realThreading;


    protected function _construct() {

        $threadPoolSize = Mage::getStoreConfig('searchperience/searchperience/threadPoolSize');
        $threadPoolSize = max(1, $threadPoolSize);
        $threadPoolSize = min(20, $threadPoolSize);

        $this->threadPool = new Threadi_Pool($threadPoolSize);

        parent::_construct();
    }


    /**
     * Process batch replacement that takes care of multi-threading and calls _processBatch internally
     *
     * @param $storeId
     * @param $productIds
     * @param array $productAttributes
     * @param array $dynamicFields
     * @param array $products
     * @param array $productRelations
     * @return array|void
     */
    public function processBatch($storeId, $productIds, array $productAttributes, array $dynamicFields, array $products, array $productRelations) {
        // Wait until there is a free slot in the pool
        $this->threadPool->waitTillReady();

        // create new thread
        $this->threadCounter++;
        $thread = Threadi_ThreadFactory::getThread(array($this, '_processBatch'));

        $this->realThreading = !($thread instanceof Threadi_Thread_NonThread);

        $thread->start($storeId, $productIds, $productAttributes, $dynamicFields, $products, $productRelations);

        // append it to the pool
        $this->threadPool->add($thread);

        // the main thread's connection also doesn't work anymore...
        Mage::getSingleton('core/resource')->getConnection('core_write')->closeConnection();
    }

    /**
     * @param $storeId
     * @param $productIds
     * @param array $productAttributes
     * @param array $dynamicFields
     * @param array $products
     * @param array $productRelations
     * @return array
     */
    public function _processBatch($storeId, $productIds, array $productAttributes, array $dynamicFields, array $products, array $productRelations) {
        if ($this->realThreading) {
            Mage::getSingleton('core/resource')->getConnection('core_write')->closeConnection();
            $this->_connections = array(); // delete cached connections

            if (class_exists('Enterprise_Index_Model_Lock')) {
                try {
                    Enterprise_Index_Model_Lock::getInstance()->shutdownReleaseLocks();
                } catch (Exception $e) {
                    // we don't care
                }
            }
        }
        return parent::_processBatch($storeId, $productIds, $productAttributes, $dynamicFields, $products, $productRelations);
    }


    protected function finishProcessing()
    {
        $this->threadPool->waitTillAllReady();

        $res = Mage::getSingleton('enterprise_index/resource_lock_resource'); /* @var $res Aoe_Searchperience_Model_Resource_Lock_Resource */
        if (is_object($res) && $res instanceof Aoe_Searchperience_Model_Resource_Lock_Resource) {
            $res->closeConnections();
        }
    }

}


