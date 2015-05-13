<?php
/**
 * class Aoe_Searchperience_Helper_Cockpit_Widget_Bundle_Config
 *
 * Cockpit Widget Bundle Config Helper
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
class Aoe_Searchperience_Helper_Cockpit_Widget_Bundle_Config extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_PATH = 'searchperience/searchperience_cp_widget_bundle/';

    /**
     * Get the configured remote js url
     *
     * @param Mage_Core_Model_Store|int $store Store for config selection
     * @return string
     */
    public function getRemoteJsUrl($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH . 'remote_js', $store);
    }

    /**
     * Get the configured widget endpoint url
     *
     * @param Mage_Core_Model_Store|int $store Store for config selection
     * @return string
     */
    public function getEndpointUrl($store = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH . 'endpoint_url', $store);
    }
}
