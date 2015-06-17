<?php
/**
 * Log Helper
 */
class Aoe_Searchperience_Helper_Log extends Mage_Core_Helper_Abstract
{

    public function log_getSearchableProducts($productIds, $requestedProductIds, $storeId) {
        if (Mage::helper('aoe_searchperience')->isLoggingEnabled()) {
            $message = sprintf('Found "%s" searchable product(s) in store "%s".', count($productIds), $storeId);
            if (!is_null($requestedProductIds)) { $message .= ' (requested productIds: ' . implode(', ',$requestedProductIds) . ')'; }

            $i = 0; $tmp = array();
            foreach ($productIds as $productId) {
                $tmp[] = $productId;
                if ($i++ > 5) {  $tmp[] = '...'; break; }
            }
            $message .= ' (found productIds: ' . implode(', ', $tmp) . ')';
            Mage::log($message, Zend_Log::DEBUG, Aoe_Searchperience_Helper_Data::LOGFILE);
        }
    }

}
