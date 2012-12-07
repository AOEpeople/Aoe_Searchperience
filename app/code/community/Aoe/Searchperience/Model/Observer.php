<?php
class Aoe_Searchperience_Model_Observer{



	/**
	 * Store searchable attributes at adapter to avoid new collection load there
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function storeSearchableAttributes(Varien_Event_Observer $observer)
	{
		$engine     = $observer->getEvent()->getEngine();
		$attributes = $observer->getEvent()->getAttributes();
		if (!$engine || !$attributes) {
			return;
		}

		foreach ($attributes as $attribute) {
			if (!$attribute->usesSource()) {
				continue;
			}

			$optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
				->setAttributeFilter($attribute->getAttributeId())
				->setPositionOrder(Varien_Db_Select::SQL_ASC, true)
				->load();

			$optionsOrder = array();
			foreach ($optionCollection as $option) {
				$optionsOrder[] = $option->getOptionId();
			}
			$optionsOrder = array_flip($optionsOrder);

			$attribute->setOptionsOrder($optionsOrder);
		}

		$engine->storeSearchableAttributes($attributes);
	}

}