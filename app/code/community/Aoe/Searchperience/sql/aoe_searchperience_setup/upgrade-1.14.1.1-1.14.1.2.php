<?php

Mage::log(sprintf('Running Upgrade Script: %s.', __FILE__));

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

$installer->getConnection()->addColumn(
    $this->getTable('sitemap/sitemap'),
    'sitemap_cms_only',
    "smallint(5) UNSIGNED NOT NULL default '0'"
);

$installer->endSetup();
