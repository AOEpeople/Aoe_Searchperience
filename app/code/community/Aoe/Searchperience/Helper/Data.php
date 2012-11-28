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
}
