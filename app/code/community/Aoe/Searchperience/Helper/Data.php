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
    /**@+
     * Config paths to retrieve Aoe_Searchperience settings
     *
     * @var string
     */
    const XML_PATH_ENABLE_DEBUG_MODE           = 'searchperience/searchperience/enableDebuggingMode';
    const XML_PATH_ENABLE_DOCUMENT_DELETION    = 'searchperience/searchperience/enableDocumentDeletion';
    const XML_PATH_ENABLE_TRACKING             = 'searchperience/searchperience/enableRecommendationTracking';
    const XML_PATH_ENABLE_WIDGETS              = 'searchperience/searchperience/enableWidgets';
    const XML_PATH_TRACKING_SCRIPT_URL         = 'searchperience/searchperience/recommendationTrackingScriptUrl';
    const XML_PATH_WIDGETS_SCRIPT_URL          = 'searchperience/searchperience/widgetsScriptUrl';
    const XML_PATH_CATEGORY_RENDERING_ENDPOINT = 'searchperience/searchperience_category_rendering/endpoint_url';
    const XML_PATH_CATEGORY_RENDERING_DEFAULT  = 'searchperience/searchperience_category_rendering/rendering_default';
    /**@-*/

    const RENDERING_DEFAULT = 0;
    const RENDERING_MAGENTO = 1;
    const RENDERING_SEARCHPERIENCE = 2;

    CONST LOGFILE = 'searchperience.log';

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
     * Holds results for checking if full documents should be logged
     *
     * @var boolean
     */
    protected $_logFullDocuments = null;

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

    /**
     * Holds recommendation tracking script url
     *
     * @var boolean
     */
    protected $_recommendationTrackingScriptUrl = null;

    /**
     * Holds result for checking if widgets are enabled
     *
     * @var boolean
     */
    protected $_widgetsEnabled = null;

    /**
     * Holds widgets script url
     *
     * @var boolean
     */
    protected $_widgetsScriptUrl = null;

    /**
     * Holds url for category rendering endpoint
     *
     * @var string|null
     */
    protected $_categoryRenderingEndpoint = null;

    /**
     * Holds value which category rendering default we should use
     *
     * @var string|null
     */
    protected $_categoryRenderingDefault = null;

    /**
     * Check whether current magento installation is EE
     *
     * @return bool
     */
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
            $valueFromSettings = Mage::getStoreConfig(self::XML_PATH_ENABLE_DEBUG_MODE);
            $this->_loggingEnabled = ((null === $valueFromSettings) ? 0 : $valueFromSettings);
        }
        return $this->_loggingEnabled;
    }

    /**
     * Returns boolean value if logging of this module is enabled
     *
     * @return bool
     */
    public function isLogFullDocumentsEnabled()
    {
        if (null === $this->_logFullDocuments) {
            $valueFromSettings = Mage::getStoreConfig('searchperience/searchperience/logFullDocuments');
            $this->_logFullDocuments = ((null === $valueFromSettings) ? 0 : $valueFromSettings);
        }
        return $this->_logFullDocuments;
    }

    /**
     * Returns boolean value if deletion of documents of this module is enabled
     *
     * @return bool
     */
    public function isDeletionEnabled()
    {
        if (null === $this->_deletionEnabled) {
            $this->_deletionEnabled = Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_DOCUMENT_DELETION);
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
            $this->_recommendationTrackingEnabled = Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_TRACKING);
        }
        return $this->_recommendationTrackingEnabled;
    }

    /**
     * Get recommendation tracking script url
     *
     * @return string
     */
    public function getRecommendationTrackingScriptUrl()
    {
        if (null === $this->_recommendationTrackingScriptUrl) {
            $this->_recommendationTrackingScriptUrl
                = rtrim(Mage::getStoreConfig(self::XML_PATH_TRACKING_SCRIPT_URL), '/') . '/';
        }
        return $this->_recommendationTrackingScriptUrl;
    }

    /**
     * Returns boolean value if widget feature enabled
     *
     * @return bool
     */
    public function isWidgetFeatureEnabled()
    {
        if (null === $this->_widgetsEnabled) {
            $this->_widgetsEnabled = Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_WIDGETS);
        }
        return $this->_widgetsEnabled;
    }

    /**
     * Get widgets script url
     *
     * @return string
     */
    public function getWidgetsScriptUrl()
    {
        if (null === $this->_widgetsScriptUrl) {
            $this->_widgetsScriptUrl = trim(Mage::getStoreConfig(self::XML_PATH_WIDGETS_SCRIPT_URL));
        }
        return $this->_widgetsScriptUrl;
    }

    /**
     * Returns category rendering endpoint url
     *
     * @return string
     */
    public function getCategoryRenderingEndpoint()
    {
        if (null === $this->_categoryRenderingEndpoint) {
            $this->_categoryRenderingEndpoint = trim(Mage::getStoreConfig(self::XML_PATH_CATEGORY_RENDERING_ENDPOINT));
        }
        return $this->_categoryRenderingEndpoint;
    }

    /**
     * Returns category rendering default
     *
     * @return string
     */
    public function getCategoryRenderingDefault()
    {
        if (null === $this->_categoryRenderingDefault) {
            $this->_categoryRenderingDefault = trim(Mage::getStoreConfig(self::XML_PATH_CATEGORY_RENDERING_DEFAULT));
        }
        return $this->_categoryRenderingDefault;
    }

    /**
     * @return bool
     */
    public function shouldCurrentCategoryBeRenderedBySearchperience()
    {
        if (!($category = Mage::registry('current_category'))) {
            return false;
        }

        $renderMode = $category->getSearchperienceCatRenderDef() ?: $this->getCategoryRenderingDefault();

        if ($renderMode == self::RENDERING_SEARCHPERIENCE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve date value as timestamp
     *
     * @param int $storeId
     * @param string $date
     * @param string $attributeName
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

    /**
     * @param string $errorMessage
     */
    public function addError($errorMessage)
    {
        // if we are not in cli script - add message to session
        if (Mage::app()->getFrontController()->getRequest()->getActionName()) {
            Mage::getSingleton('core/session')->addError($errorMessage);
        }
    }

    /**
     * Helper method for sorting children categories by product count
     *
     * @param Mage_Catalog_Model_Category $objectA First category object
     * @param Mage_Catalog_Model_Category $objectB Second category object
     * @return int
     */
    public function sortChildrenCategoriesByProductCount($objectA, $objectB)
    {
        if ($objectA->getProductCount() == $objectB->getProductCount()) {
            return 0;
        }

        return (($objectA->getProductCount() < $objectB->getProductCount()) ? 1 : -1);
    }
}
