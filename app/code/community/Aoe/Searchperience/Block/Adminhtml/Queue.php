<?php

class Aoe_Searchperience_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct()
	{
		$this->_controller = 'adminhtml_queue';
		$this->_blockGroup = 'aoe_searchperience';
		$this->_headerText = Mage::helper('aoe_searchperience')->__('Queue items');
		//$this->_addButtonLabel = Mage::helper('aoecoupons')->__('Add Gift Card Account');
		parent::__construct();
		$this->removeButton('add');
	}
}
