<?php

require_once 'abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Compiler extends Mage_Shell_Abstract
{
    /**
     * Get Indexer instance
     *
     * @return Mage_Index_Model_Indexer
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('index/indexer');
    }

    /**
     * Parse string with indexers and return array of indexer instances
     *
     * @param string $string
     * @return array
     */
    protected function _parseIndexerString($string)
    {
        $processes = array();
        if ($string == 'all') {
            $collection = $this->_getIndexer()->getProcessesCollection();
            foreach ($collection as $process) {
                $processes[] = $process;
            }
        } else if (!empty($string)) {
            $codes = explode(',', $string);
            foreach ($codes as $code) {
                $process = $this->_getIndexer()->getProcessByCode(trim($code));
                if (!$process) {
                    echo 'Warning: Unknown indexer with code ' . trim($code) . "\n";
                } else {
                    $processes[] = $process;
                }
            }
        }
        return $processes;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('info')) {
            $processes = $this->_parseIndexerString('all');
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                echo sprintf('%-30s', $process->getIndexerCode());
                echo $process->getIndexer()->getName() . "\n";
            }
        } else if ($this->getArg('status') || $this->getArg('mode')) {
            if ($this->getArg('status')) {
                $processes  = $this->_parseIndexerString($this->getArg('status'));
            } else {
                $processes  = $this->_parseIndexerString($this->getArg('mode'));
            }
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                $status = 'unknown';
                if ($this->getArg('status')) {
                    switch ($process->getStatus()) {
                        case Mage_Index_Model_Process::STATUS_PENDING:
                            $status = 'Pending';
                            break;
                        case Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX:
                            $status = 'Require Reindex';
                            break;

                        case Mage_Index_Model_Process::STATUS_RUNNING:
                            $status = 'Running';
                            break;

                        default:
                            $status = 'Ready';
                            break;
                    }
                } else {
                    switch ($process->getMode()) {
                        case Mage_Index_Model_Process::MODE_REAL_TIME:
                            $status = 'Update on Save';
                            break;
                        case Mage_Index_Model_Process::MODE_MANUAL:
                            $status = 'Manual Update';
                            break;
                        case Aoe_Index_Model_Process::MODE_QUEUED:
                            $status = "Queue Events";
                            break;
                    }
                }
                echo sprintf('%-30s ', $process->getIndexer()->getName() . ':') . $status ."\n";

            }
        } else if ($this->getArg('status-reindex_required')) {
            $processes = $this->_parseIndexerString($this->getArg('status-reindex_required'));
            $status    = Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX;

            foreach ($processes as $process) {
                try {
                    $process->setStatus($status)->save();
                    echo $process->getIndexer()->getName() . " index status has successfully been changed\n";
                } catch (Mage_Core_Exception $e) {
                    echo $e->getMessage() . "\n";
                } catch (Exception $e) {
                    echo $process->getIndexer()->getName() . " index process unknown error:\n";
                    echo $e . "\n";
                }
            }
        }
        else if ($this->getArg('mode-realtime') || $this->getArg('mode-manual') || $this->getArg('mode-queued')) {
            if ($this->getArg('mode-realtime')) {
                $mode       = Mage_Index_Model_Process::MODE_REAL_TIME;
                $processes  = $this->_parseIndexerString($this->getArg('mode-realtime'));
            } else if ($this->getArg('mode-manual')) {
                $mode       = Mage_Index_Model_Process::MODE_MANUAL;
                $processes  = $this->_parseIndexerString($this->getArg('mode-manual'));
            }
            else {
                $mode       = Aoe_Index_Model_Process::MODE_QUEUED;
                $processes  = $this->_parseIndexerString($this->getArg('mode-queued'));
            }
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                try {
                    $process->setMode($mode)->save();
                    echo $process->getIndexer()->getName() . " index was successfully changed index mode\n";
                } catch (Mage_Core_Exception $e) {
                    echo $e->getMessage() . "\n";
                } catch (Exception $e) {
                    echo $process->getIndexer()->getName() . " index process unknown error:\n";
                    echo $e . "\n";
                }
            }
        } else if ($this->getArg('reindex') || $this->getArg('reindexall')) {
            if ($this->getArg('reindex')) {
                $processes = $this->_parseIndexerString($this->getArg('reindex'));
            } else {
                $processes = $this->_parseIndexerString('all');
            }

            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                try {
                    $process->reindexEverything();
                    echo $process->getIndexer()->getName() . " index was rebuilt successfully\n";
                } catch (Mage_Core_Exception $e) {
                    echo $e->getMessage() . "\n";
                } catch (Exception $e) {
                    echo $process->getIndexer()->getName() . " index process unknown error:\n";
                    echo $e . "\n";
                }
            }

        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f indexer.php -- [options]

  --status <indexer>                    Show Indexer(s) Status
  --status-reindex_required <indexer>   Set status to "Reindex Required"
  --mode <indexer>                      Show Indexer(s) Index Mode
  --mode-realtime <indexer>             Set index mode type "Update on Save"
  --mode-manual <indexer>               Set index mode type "Manual Update"
  --mode-queued <indexer>               Set index mode type "Queue Events"
  --reindex <indexer>                   Reindex Data
  info                                  Show allowed indexers
  reindexall                            Reindex Data by all indexers
  help                                  This help

  <indexer>     Comma separated indexer codes or value "all" for all indexers

USAGE;
    }
}

$shell = new Mage_Shell_Compiler();
$shell->run();