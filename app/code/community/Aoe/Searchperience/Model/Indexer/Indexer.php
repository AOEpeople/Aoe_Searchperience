<?php

class Aoe_Searchperience_Model_Indexer_Indexer
{
    /**
     * Reindex of catalog search fulltext index using search engine
     *
     * @return Aoe_Searchperience_Model_Indexer_Indexer
     */
    public function reindexAll()
    {
        /* Change index status to running */
        /* @var $indexProcess Mage_Index_Model_Process */
        $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
        if ($indexProcess) {
            $indexProcess->setForcePartialReindex(TRUE);
            $indexProcess->reindexAll();
        }

        return $this;
    }
}
