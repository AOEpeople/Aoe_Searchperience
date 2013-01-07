<?php
/**
 * default helper class
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Holds result for checking if logging is enabled
     *
     * @var boolean
     */
    protected $_loggingEnabled = null;

    /**
     * Holds result for checking if deletion is enabled
     *
     * @var boolean
     */
    protected $_deletionEnabled = null;

    public function isEnterprise()
    {
        return $this->isModuleEnabled('Enterprise_Search');
    }

    /**
     * Returns unique identifier for product for given store
     *
     * @param $productId
     * @param $storeId
     * @return string
     */
    public function getProductUniqueId($productId, $storeId)
    {
        return $productId . '_' . $storeId;
    }

    /**
     * Returns boolean value if logging of this module is enabled
     *
     * @return bool
     */
    public function isLoggingEnabled()
    {
        if (null === $this->_loggingEnabled) {
            $valueFromSettings = Mage::getStoreConfig('searchperience/searchperience/enableDebuggingMode');
            $this->_loggingEnabled = ((null === $valueFromSettings) ? 0 : $valueFromSettings);
        }
        return $this->_loggingEnabled;
    }

    /**
     * Returns boolean value if deletion of documents of this module is enabled
     *
     * @return bool
     */
    public function isDeletionEnabled()
    {
        if (null === $this->_deletionEnabled) {
            $valueFromSettings = Mage::getStoreConfig('searchperience/searchperience/enableDocumentDeletion');
            $this->_deletionEnabled = ((null === $valueFromSettings) ? 0 : $valueFromSettings);
        }
        return $this->_deletionEnabled;
    }
}
