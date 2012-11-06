<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
* Create table 'aoe_searchperience/queue_item'
*/
if( $installer->tableExists($installer->getTable('aoe_searchperience/queue_item'))) {
	$installer->getConnection()->dropTable($installer->getTable('aoe_searchperience/queue_item'));
}
$table = $installer->getConnection()

	->newTable($installer->getTable('aoe_searchperience/queue_item'))
	->addColumn('queue_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
	), 'Queue Item Id')
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
	), 'Model Id')
	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
	), 'Updated At')
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
	'unsigned'  => true,
	), 'Store Id')
	->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
	), 'Model type')
	->addForeignKey($installer->getFkName('aoe_searchperience/queue_item', 'store_id', 'core/store', 'store_id'),
	'store_id', $installer->getTable('core/store'), 'store_id',
	Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE);


$installer->getConnection()->createTable($table);

$installer->endSetup();
