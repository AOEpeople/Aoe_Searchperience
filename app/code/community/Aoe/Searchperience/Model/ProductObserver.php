<?php
/**
 * listen to product changes
 *
 * @category    Aoe
 * @package     Aoe_Searchperience
 * @author      Christoph Frenes <christoph.frenes@aoemedia.de>
 */
class Aoe_Searchperience_Model_ProductObserver
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * execute whenever product is saved (check for changed stock, status and visibility)
     *
     * @param Varien_Event_Observer $observer
     */
    public function productChange(Varien_Event_Observer $observer)
    {
        $this->_product = $observer->getEvent()->getProduct();

        switch (true) {
            // product enabled, in stock if managed, visible in 'catalog&search' or just in 'search'
            case $this->_product->getStatus() && $this->_product->getStockItem()->getIsInStock() && ($this->_product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH || $this->_product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH):
            // product enabled, stock not managed, visible in 'catalog&search' or just in 'search'
            //case $this->_product->getStatus() && !$this->_product->getStockItem()->getManageStock() && ($this->_product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH || $this->_product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH):
                // update/index product
                Mage::log(sprintf('noticed change for product "%s" with id "%d" - update!', $this->_product->getName(), $this->_product->getId()));
                break;
            default:
                // remove product
                Mage::log($this->_product->getStatus() . ' - ' . $this->_product->getStockItem()->getIsInStock() . ' - ' . $this->_product->getVisibility());
                Mage::log(sprintf('noticed change for product "%s" with id "%d" - remove!', $this->_product->getName(), $this->_product->getId()));
                break;
        }
    }

    /**
     * listen for deleted products
     *
     * @param Varien_Event_Observer $observer
     */
    public function afterDelete(Varien_Event_Observer $observer)
    {
        Mage::log(sprintf('noticed removed product "%s" with id "%d" - remove!', $this->_product->getName(), $this->_product->getId()));
        $this->_product = $observer->getEvent()->getProduct();

    }

    protected function indexProduct($productId)
    {

    }

    protected function deleteProduct($productId)
    {

    }
}
