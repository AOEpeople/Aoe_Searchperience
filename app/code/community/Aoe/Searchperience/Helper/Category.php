<?php
/**
 * Category Helper
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Fabrizio Branca
 */
class Aoe_Searchperience_Helper_Category extends Mage_Core_Helper_Abstract
{

    /**
     * @var array of categories $categories[store][categoryId]=>category data
     */
    protected $categories = array();

    /**
     * Fetches all categories to local cache
     * @param $storeId
     */
    protected function getCategoriesForStore($storeId) {

        if (!isset($this->categories[$storeId])) {
            /** @var $categoryCollection Mage_Catalog_Model_Resource_Category_Collection */
            $categoryCollection = Mage::getResourceModel('catalog/category_collection');
            $categoryCollection->setStoreId($storeId)
                ->addNameToResult()
                ->addIsActiveFilter()
                ->setLoadProductCount(FALSE);
            $categories = $categoryCollection->load()->toArray(array('path','level','name'));
            unset($categoryCollection);

            $this->categories[$storeId] = $categories;
        }
        return $this->categories[$storeId];
    }

    /**
     * @param $categoryId
     * @param $storeId
     * @param bool $addCounter
     * @return mixed
     */
    public function getPathForCategory($categoryId, $storeId, $addCounter=false) {

        $categories = $this->getCategoriesForStore($storeId);

        if (!isset($categories[$categoryId])) {
            return false;
        }

        $category = $categories[$categoryId]; /* @var $category array */

        $pathCategories = explode('/', $category['path']);
        $path = array();
        array_shift($pathCategories); //don't need root category
        foreach ($pathCategories as $pathCategoryId) {
            if (isset($categories[$pathCategoryId])) {
                $cat = $categories[$pathCategoryId]; /* @var $cat array */
                if ($cat['level'] > 1) {
                    $pathPart = $cat['name'];
                    $pathPart = str_replace('/','\/', $pathPart);
                    $path[] = $pathPart;
                }
            }
        }

        $pathString = implode('/', $path);

        if ($addCounter) {
            $counter = count($path) - 1;
            $pathString = $counter . '-' . $pathString;

        }
        return $pathString;
    }


}
