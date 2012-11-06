<?php

class Aoe_Searchperience_Model_Resource_Queue_Item_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	public function _construct() {
		$this->_init('aoe_searchperience/queue_item');
	}

}