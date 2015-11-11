<?php

/**
 * Class Aoe_Searchperience_Block_Catalog_Category_Navigation
 *
 * @category Aoe
 * @package  Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
class Aoe_Searchperience_Block_Catalog_Category_Navigation extends Mage_Core_Block_Template
{
    protected $_subcategories = null;
    protected $_template = 'aoesearchperience/catalog/category/subcategories.phtml';

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getSubcategories()
    {
        if (is_null($this->_subcategories)) {
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::registry('current_category');

            $this->_subcategories = $category->getResource()->getChildrenCategories($category)->getItems();
            usort($this->_subcategories, [Mage::helper('aoe_searchperience'), 'sortChildrenCategoriesByProductCount']);
        }

        return $this->_subcategories;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (empty($this->getSubcategories())) {
            return '';
        } else {
            return parent::_toHtml();
        }
    }
}
