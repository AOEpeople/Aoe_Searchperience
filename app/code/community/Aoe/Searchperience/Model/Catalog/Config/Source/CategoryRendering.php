<?php

class Aoe_Searchperience_Model_Catalog_Config_Source_CategoryRendering extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        return [
            [
                'value' => Aoe_Searchperience_Helper_Data::RENDERING_DEFAULT,
                'label' => 'Default Settings',
            ],
            [
                'value' => Aoe_Searchperience_Helper_Data::RENDERING_MAGENTO,
                'label' => 'Magento Rendering',
            ],
            [
                'value' => Aoe_Searchperience_Helper_Data::RENDERING_SEARCHPERIENCE,
                'label' => 'Searchperience Rendering',
            ],
        ];
    }
}
