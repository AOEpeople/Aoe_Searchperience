<?php

require_once 'Threadi/Loader.php';

class Aoe_Searchperience_Model_Resource_Fulltext_Threadi extends Aoe_Searchperience_Model_Resource_Fulltext
{

    /**
     * Thread pool size
     *
     * @var int
     */
    protected $threadPoolSize = 1;

    /**
     * @var Threadi_Pool
     */
    protected $threadPool;

    /**
     * @var int thread counter
     */
    protected $threadCounter = 0;


    protected function _construct() {
        $this->threadPool = new Threadi_Pool($this->threadPoolSize);
        // TODO: read $threadPoolSize and $limit from configuration
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

        if (!$thread instanceof Threadi_Thread_NonThread) {
            Mage::getSingleton('core/resource')->getConnection('core_write')->closeConnection();
            $this->_connections = array(); // delete cached connections

            if (class_exists('Enterprise_Index_Model_Lock')) {
                Enterprise_Index_Model_Lock::getInstance()->shutdownReleaseLocks();
            }
        }

        $thread->start($storeId, $productIds, $productAttributes, $dynamicFields, $products, $productRelations);

        // append it to the pool
        $this->threadPool->add($thread);
    }


    protected function finishProcessing()
    {
        $this->threadPool->waitTillAllReady();
    }

}
