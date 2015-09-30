<?php

// @codingStandardsIgnoreStart
require Mage::getBaseDir('lib') . DS . 'searchperience' . DS . 'autoload.php';
// @codingStandardsIgnoreEnd

/**
 * Class Aoe_Searchperience_Block_Catalog_Category_View
 *
 * @category Aoe
 * @package  Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
class Aoe_Searchperience_Block_Catalog_Category_View extends Mage_Core_Block_Template
{
    protected $_content;

    /**
     * @return string
     */
    protected function _fetchCategoryHtml()
    {
        $catId = Mage::registry('current_category')->getId();
        $url = Mage::helper('aoe_searchperience')->getCategoryRenderingEndpoint();

        $url = str_replace('###ID###', $catId, $url);
        //http://www.search.host/index.php?id=34&searchperience[filters][pr_categoryids][equals]=' . $catId . '&eID=tx_aoesolr_search';

        try {
            $client = new Guzzle\Http\Client();
            $options = [
                'connect_timeout' => 1.0,
                'timeout' => 5.0,
            ];
            $request = $client->get($url, null, $options);
            $content = $client->send($request)->getBody();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('aoestatic/cache_control')->addMaxAge(60);
            $this->setCacheLifetime(60);
            $content = $this->__('This page is currently under maintenance and will be available shortly!');
        }

        return $content;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $subcategories = $this->getChild('subcategory_navigation')->toHtml();

        return str_replace('<!-- CATEGORY_LIST_PLACEHOLDER -->', $subcategories, $this->_fetchCategoryHtml());
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            __FILE__,
            'CATEGORY-' . Mage::registry('current_category')->getId(),
            Mage::helper('aoe_searchperience')->getCategoryRenderingEndpoint(),
        ];
    }
}
