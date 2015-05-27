<?php

Mage::log(sprintf('Running Upgrade Script: %s.', __FILE__));

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

//$installer->removeAttribute('catalog_category', 'searchperience_cat_render_url');
//$installer->removeAttribute('catalog_category', 'searchperience_cat_render_def');

$installer->addAttribute(
    'catalog_category',
    'searchperience_cat_render_url',
    array(
        'group'        => 'Searchperience Category-Rendering',
        'type'         => 'varchar',
        'label'        => 'Category-Rendering-Endpoint',
        'input'        => 'text',
        'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'      => true,
        'required'     => false,
        'user_defined' => false,
        'default'      => ''
    )
);

$installer->addAttribute(
    'catalog_category',
    'searchperience_cat_render_def',
    array(
        'group'        => 'Searchperience Category-Rendering',
        'type'         => 'int',
        'label'        => 'Category-Rendering Default',
        'input'        => 'select',
        'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'      => true,
        'required'     => false,
        'user_defined' => false,
        'default_value' => '0',
        'default'      => '0',
        'option' => array(
            'values' => array(
                0 => 'Default Rendering',
                1 => 'Magento Rendering',
                2 => 'Searchperience Rendering',
            )
        ),
    )
);

$installer->endSetup();
