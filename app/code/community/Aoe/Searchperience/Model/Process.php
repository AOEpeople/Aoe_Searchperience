<?php


class Aoe_Searchperience_Model_Process extends Mage_Index_Model_Process {

    const MODE_QUEUED = 'queued';

    /**
     * Adding new option to the modes options
     *
     * @return array
     */
    public function getModesOptions() {
        $modesOptions = parent::getModesOptions();
        $modesOptions[self::MODE_QUEUED] = Mage::helper('index')->__('Queue Events');
        return $modesOptions;
    }

	/**
	 * Reindex all data
	 * if process status is "pending" than we process only the event queue, else
	 * if process status is "index_required" we are reindexing everything
	 *
	 */
	public function reindexAll()
	{
		if ($this->getMode() !== self::MODE_QUEUED) {
			return parent::reindexAll();
		}

		if ($this->isLocked()) {
			Mage::throwException(Mage::helper('index')->__('%s Index process is working now. Please try run this process later.', $this->getIndexer()->getName()));
		}

		$processStatus = $this->getStatus();

		$this->_getResource()->startProcess($this);
		$this->lock();
		$processedSomething = TRUE;
		try {
			$eventsCollection = $this->getUnprocessedEventsCollection();

			/** @var $eventResource Mage_Index_Model_Resource_Event */
			$eventResource = Mage::getResourceSingleton('index/event');

			if ($processStatus == self::STATUS_PENDING || $this->getForcePartialReindex()) {
				if ($eventsCollection->count() > 0) {
					$this->_getResource()->beginTransaction();
					try {
						$this->_processEventsCollection($eventsCollection, false);
						$this->_getResource()->commit();
					} catch (Exception $e) {
						$this->_getResource()->rollBack();
						throw $e;
					}
				} else {
					$processedSomething = FALSE;
				}
			} else {
				//Update existing events since we'll do reindexAll
				$eventResource->updateProcessEvents($this);
				$this->getIndexer()->reindexAll();
			}
			$this->unlock();

			$this->_getResource()->endProcess($this);
		} catch (Exception $e) {
			$this->unlock();
			$this->_getResource()->failProcess($this);
			throw $e;
		}
		if($processedSomething) {
			//we want to dispatch this event only if we actually processed sth
			Mage::dispatchEvent('after_reindex_process_' . $this->getIndexerCode());
		}
	}
}