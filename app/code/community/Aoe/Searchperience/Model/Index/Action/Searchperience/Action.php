<?php

abstract class Aoe_Searchperience_Model_Index_Action_Searchperience_Action implements Enterprise_Mview_Model_Action_Interface
{

    /**
     * Last version ID
     *
     * @var int
     */
    protected $_lastVersionId;

    /**
     * Connection instance
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Mview metadata instance
     *
     * @var Enterprise_Mview_Model_Metadata
     */
    protected $_metadata;

    /**
     * Mview factory instance
     *
     * @var Enterprise_Mview_Model_Factory
     */
    protected $_factory;

    /**
     * Application instance
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Changed IDs (only if this action is used as changelog action model)
     *
     * @var array
     */
    protected $_changedIds;


    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'metadata' Enterprise_Mview_Model_Metadata
     *  - 'connection' Varien_Db_Adapter_Interface
     *  - 'factory' Enterprise_Mview_Model_Factory
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->_connection = $args['connection'];
        $this->_metadata   = $args['metadata'];
        $this->_factory    = $args['factory'];
        $this->_app = !empty($args['app']) ? $args['app'] : Mage::app();
    }

    /**
     * Return last version ID
     *
     * @return string
     */
    protected function _getLastVersionId()
    {
        $changelogName = $this->_metadata->getChangelogName();
        if (empty($changelogName)) {
            return 0;
        }

        if (!$this->_lastVersionId) {
            $select = $this->_connection->select()
                ->from($changelogName, array('version_id'))
                ->order('version_id DESC')
                ->limit(1);

            $this->_lastVersionId = (int)$this->_connection->fetchOne($select);
        }
        return $this->_lastVersionId;
    }

    /**
     * This is where the magic happens. Implement this in the inheriting class
     *
     * @return mixed
     */
    abstract protected function _execute();

    /**
     * Wrapper method to _execute.
     * This will take care of everything that's changelog and metadata related.
     *
     * @return $this|Enterprise_Mview_Model_Action_Interface
     * @throws Enterprise_Index_Model_Action_Exception
     */
    public function execute() {

        if (!$this->_metadata->isValid()) {
            throw new Enterprise_Index_Model_Action_Exception("Can't perform operation, incomplete metadata!");
        }

        try {
            $this->_getLastVersionId();
            $this->_metadata->setInProgressStatus()->save();

            $this->_execute();

            $this->_updateMetadata();
        } catch (Exception $e) {
            Mage::logException($e);
            if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
                Mage::log(sprintf('Setting invalid status because of exception "%s"', $e->getMessage()), Zend_Log::ERR, Aoe_Searchperience_Helper_Data::LOGFILE);
            }
            $this->_metadata->setInvalidStatus()->save();
            throw new Enterprise_Index_Model_Action_Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * Set changelog valid and update version id into metedata
     *
     * @return Enterprise_Index_Model_Action_Abstract
     */
    protected function _updateMetadata()
    {
        if ($this->_metadata->getStatus() == Enterprise_Mview_Model_Metadata::STATUS_IN_PROGRESS) {
            $this->_metadata->setValidStatus();
        }
        $this->_metadata->setVersionId($this->_getLastVersionId())->save();
        return $this;
    }

    /**
     * Get change IDs (from changelog table)
     * Only available if this action is used as changelog action model
     *
     * @return array
     */
    protected function getChangeIds()
    {
        if (is_null($this->_changedIds)) {
            /* @var $changelog Aoe_Searchperience_Model_Changelog */
            $changelog = $this->_factory->getModel(
                'aoe_searchperience/changelog',
                array(
                    'connection' => $this->_connection,
                    'metadata' => $this->_metadata
                )
            );
            $this->_changedIds = $changelog->loadByMetadata($this->_getLastVersionId());
            $this->_lastVersionId = $changelog->getLastProcessedVersionId();
            $this->_changedIds = array_unique($this->_changedIds);
        }
        return $this->_changedIds;
    }




    /**
     * DATABASE STUFF ...
     */

    /**
     * Proxy for resource getTable()
     *
     * @param string $entityName
     * @return string
     */
    protected function _getTable($entityName)
    {
        return $this->_metadata->getResource()->getTable($entityName);
    }

    /**
     * Return read connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {
        return $this->_metadata->getResource()->getReadConnection();
    }

    /**
     * Return write connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        return $this->_connection;
    }



}
