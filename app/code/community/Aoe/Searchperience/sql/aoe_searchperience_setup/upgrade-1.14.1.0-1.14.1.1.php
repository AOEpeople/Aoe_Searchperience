<?php

Mage::log(sprintf('Running Upgrade Script: %s.', __FILE__));

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

$installer->removeAttribute('catalog_category', 'searchperience_cat_render_url');
$installer->removeAttribute('catalog_category', 'searchperience_cat_render_def');

$installer->addAttribute(
    'catalog_category',
    'searchperience_cat_render_def',
    [
        'group'         => 'Searchperience Category-Rendering',
        'type'          => 'int',
        'label'         => 'Category-Rendering Default',
        'input'         => 'select',
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'       => true,
        'required'      => false,
        'user_defined'  => false,
        'default_value' => '0',
        'default'       => '0',
        'source'        => 'aoe_searchperience/catalog_config_source_categoryRendering',
    ]
);

$installer->endSetup();
