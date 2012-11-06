<?php

class Aoe_Searchperience_Model_Resource_Queue_Item extends Mage_Core_Model_Resource_Db_Abstract {

	public function _construct() {
		$this->_init('aoesearchperience/queue_item', 'id');
	}

}