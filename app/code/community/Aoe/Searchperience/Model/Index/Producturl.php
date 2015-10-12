<?php

/**
 * Class Aoe_Searchperience_Model_Index_Producturl
 *
 * @category Model
 * @package  Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
class Aoe_Searchperience_Model_Index_Producturl extends Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Product_Refresh_Row
{
    /**
     * @param array|int $productIds reindex these ids
     * @return $this
     */
    public function setChangedIds($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        $this->_changedIds = $productIds;

        return $this;
    }

    /**
     * @return array|null
     */
    protected function _getChangedIds()
    {
        return $this->_changedIds;
    }
}
