<?php

class Aoe_Searchperience_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('queueGrid');

		$this->setSaveParametersInSession(true);
	  //  $this->setDefaultSort('created_at');
		//$this->setDefaultDir('DESC');
	}

	protected function _prepareCollection() {
		$collection = Mage::getResourceModel('aoe_searchperience/queue_item_collection');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('queue_item_id', array(
			'header' => Mage::helper('aoe_searchperience')->__('Coupon Id'),
			'width' => '80px',
			'type' => 'number',
			'index' => 'queue_item_id',
		));


		$this->addColumn('id', array(
			'header' => Mage::helper('aoe_searchperience')->__('Model id'),
			'index' => 'id',
			'type' => 'number',
		));


		$this->addColumn('type', array(
			'header' => Mage::helper('aoe_searchperience')->__('Model Type'),
			'width' => '80px',
			'type' => 'text',
			'index' => 'type',
		));

		$this->addColumn('updated_at', array(
			'header' => Mage::helper('aoe_searchperience')->__('Updated at'),
			'index' => 'updated_at',
			'type' => 'date',
			'format' =>  Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
		));

		$this->addColumn('store_id', array(
			'header' => Mage::helper('aoe_searchperience')->__('Store id'),
			'index' => 'store_id',
			'type' => 'number',
		));
        return parent::_prepareColumns();
    }
	/**
	 * Retrieve row url
	 */
	public function getRowUrl($row)
	{
		return "";
		return $this->getUrl('*/*/edit', array(
			'id'    => $row->getId()
		));
	}

}