<?php

class Aoe_Searchperience_Model_QueueProcessor {
	const QUEUE_NAME = 'searchperience';

	const PRODUCT_CALLBACK_EXPR = 'aoe_searchperience/api_documentBuilder::buildDocumentForProduct';

	public function run() {
		$apiClient = Mage::getModel('aoe_searchperience/api_client'); /* @var $apiClient Aoe_Searchperience_Model_Api_Client */

		$queue = Mage::getModel('aoe_queue/queue', array(self::QUEUE_NAME)); /* @var $queue Aoe_Queue_Model_Queue */
		$messages = $queue->receive(5); /* @var $messages Zend_Queue_Message_Iterator */
		foreach ($messages as $message) { /* @var $message Aoe_Queue_Model_Message */
			$document = $message->execute();
			$apiClient->sendDocument($document);
		}
	}
}