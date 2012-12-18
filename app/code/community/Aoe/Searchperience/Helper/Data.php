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
     * Returns boolean value if login of this module is enabled
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
}
