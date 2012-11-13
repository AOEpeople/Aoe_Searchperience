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
}
