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
     * Special cases for attribute times
     *
     * @var array
     */
    protected $_attributeTimes = array(
        'special_from_date' => array(
            'hour'   => '00',
            'minute' => '00',
            'second' => '01',
        ),
        'special_to_date' => array(
            'hour'   => '23',
            'minute' => '59',
            'second' => '59',
        ),
        'default' => array(
            'hour'   => '00',
            'minute' => '00',
            'second' => '01',
        ),
    );

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

    /**
     * Holds result for checking if recommendation tracking is enabled
     *
     * @var boolean
     */
    protected $_recommendationTrackingEnabled = null;

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

    /**
     * Returns boolean value if recommendation tracking is enabled
     *
     * @return bool
     */
    public function isRecommendationTrackingEnabled()
    {
        if (null === $this->_recommendationTrackingEnabled) {
            $valueFromSettings = Mage::getStoreConfig('searchperience/searchperience/enableRecommendationTracking');
            $this->_recommendationTrackingEnabled = ((null === $valueFromSettings) ? 0 : $valueFromSettings);
        }
        return $this->_recommendationTrackingEnabled;
    }

    /**
     * Retrieve date value as timestamp
     *
     * @param int $storeId
     * @param string $date
     * @param string $attributeName
     * @param array  $time
     *
     * @return string|false
     */
    public function getTimestampForAttribute($storeId, $date = null, $attributeName)
    {
        if (empty($date)) {
            return false;
        }

        $locale = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $storeId);
        $locale = new Zend_Locale($locale);

        try {
            $parsedDate = Zend_Locale_Format::getDate(
                $date,
                array(
                    'date_format' => $locale->getTranslation(
                        null,
                        'date',
                        $locale
                    ),
                    'locale' => $locale,
                    'format_type' => 'iso'
                )
            );
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        // set special times as defined in class variable
        $attributeTimesKey = (isset($this->_attributeTimes[$attributeName]) ? $attributeName : 'default');

        return mktime(
            $this->_attributeTimes[$attributeTimesKey]['hour'],
            $this->_attributeTimes[$attributeTimesKey]['minute'],
            $this->_attributeTimes[$attributeTimesKey]['second'],
            $parsedDate['month'],
            $parsedDate['day'],
            $parsedDate['year']
        );
    }
}
