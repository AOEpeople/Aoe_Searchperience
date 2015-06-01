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
            $request = $client->get($url);
            return $client->send($request)->getBody();
        } catch (Guzzle\Http\Exception\ServerErrorResponseException $e) {
            Mage::logException($e);
            return '<!-- CATEGORY_LIST_PLACEHOLDER -->';
        }
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
