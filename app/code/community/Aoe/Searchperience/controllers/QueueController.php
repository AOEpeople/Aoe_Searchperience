<?php

class Aoe_Searchperience_QueueController extends Mage_Adminhtml_Controller_Action {

	/**
	 * Default action
	 */
	public function indexAction()
	{
		$this->_title($this->__('Searchperience'))->_title($this->__('Queue management'));
		$this->loadLayout();
		$this->_setActiveMenu('searchperience/queue');
		$this->renderLayout();
	}

	/**
	 * Create new queue item
	 */
	public function newAction()
	{
		// the same form is used to create and edit
		$this->_forward('edit');
	}

//	/**
//	 * Edit Coupon
//	 */
//	public function editAction()
//	{
//		$id = $this->getRequest()->getParam('id');
//		$model = $this->_initCoupon();
//
//		if (!$model->getId() && $id) {
//			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aoecoupons')->__('This Coupon no longer exists.'));
//			$this->_redirect('*/*/');
//			return;
//		}
//
//		$this->_title($model->getId() ? $model->getCode() : $this->__('New Coupon'));
//
//		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
//		if (!empty($data)) {
//			$model->addData($data);
//		}
//
//		$this->loadLayout()
//			//->_addBreadcrumb($id ? Mage::helper('enterprise_giftcardaccount')->__('Edit Gift Card Account') : Mage::helper('enterprise_giftcardaccount')->__('New Gift Card Account'),
//			//$id ? Mage::helper('enterprise_giftcardaccount')->__('Edit Gift Card Account') : Mage::helper('enterprise_giftcardaccount')->__('New Gift Card Account'))
//			->_addContent($this->getLayout()->createBlock('aoecoupons/adminhtml_coupons_edit')
//				->setData('form_action_url', $this->getUrl('*/*/save')))
//			//->_addLeft($this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tabs'))
//			->renderLayout();
//	}
//
//	/**
//	 * Save action
//	 */
//	public function saveAction()
//	{
//		// check if data sent
//		if ($data = $this->getRequest()->getPost()) {
//			$this->_redirect('*/*/');
//		}
//
//		$data = $this->_filterPostData($data);
//		// init model and set data
//		$id =(int)$this->getRequest()->getParam('coupon_id');
//		$coupon = Mage::getModel('salesrule/coupon');
//		if ($id) {
//			$coupon->load($id);
//		}
//		if (!$coupon->getId() && $id) {
//			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('enterprise_giftcardaccount')->__('This Gift Card Account no longer exists.'));
//			$this->_redirect('*/*/');
//			return;
//		}
//
//		$batchQty = intval($this->getRequest()->getParam('batch_qty'));
//
//		if($batchQty <= 1) {
//			$batchQty = 1;
//		}
//
//		// try to save it
//		try {
//			if($coupon->getId()) {
//				if (!empty($data)) {
//					$coupon->addData($data);
//					Mage::register('current_coupon', $coupon);
//					$coupon->save();
//				}
//			} else {
//				$ruleId = $data['rule_id'];
//				$rule = Mage::getModel('salesrule/rule')->load($ruleId); /** @var $rule Mage_SalesRule_Model_Rule */
//				if(!$rule->getId()) {
//					Mage::throwException("Wrong Shopping cart price rule");
//				}
//				if($rule->getCouponType() != $rule::COUPON_TYPE_AUTO) {
//					Mage::throwException("Coupon type in rule definition must be set to 'Auto'");
//				}
//				for( $i=0; $i<$batchQty; $i++){
//
//					$coupon = $rule->acquireCoupon();
//					if(isset($data['usage_limit']) && intval($data['usage_limit']) > 0) {
//						$coupon->setUsageLimit( intval($data['usage_limit']));
//						$coupon->save();
//					}
//				}
//			}
//			Mage::register('current_coupon', $coupon);
//			Mage::getSingleton('adminhtml/session')->addSuccess(($i) ." ". Mage::helper('aoecoupons')->__(' coupons have been saved.'));
//			// clear previously saved data from session
//			Mage::getSingleton('adminhtml/session')->setFormData(false);
//			// check if 'Save and Continue'
//			if ($this->getRequest()->getParam('back')) {
//				$this->_redirect('*/*/edit', array('id' => $coupon->getId()));
//				return;
//			}
//			// go to grid
//			$this->_redirect('*/*/');
//			return;
//
//		} catch (Exception $e) {
//			// display error message
//			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
//			// save data in session
//			Mage::getSingleton('adminhtml/session')->setFormData($data);
//			// redirect to edit form
//			$this->_redirect('*/*/edit', array('id' => $coupon->getId()));
//			return;
//		}
//		$this->_redirect('*/*/');
//	}
//
//
//
//	/**
//	 * Load Coupon from request
//	 *
//	 * @param string $idFieldName
//	 * @return Mage_Salesrule_Model_Coupon
//	 */
//	protected function _initCoupon($idFieldName = 'id')
//	{
//		$this->_title($this->__('Promotions'))->_title($this->__('Coupons'));
//
//		$id = (int)$this->getRequest()->getParam($idFieldName);
//		$model = Mage::getModel('salesrule/coupon');
//		if ($id) {
//			$model->load($id);
//		}
//		Mage::register('current_coupon', $model);
//		return $model;
//	}
//
	/**
	 * Check the permission to run it
	 *
	 * @return boolean
	 */
	protected function _isAllowed()
	{
		return  Mage::getSingleton('admin/session')->isAllowed('searchperience/queue');
	}
//
//	protected function _filterPostData($data) {
//		$data = $this->_filterDates($data, array('expiration_date'));
//		return $data;
//	}
//
//	public function exportCsvAction(){
//		$fileName   = 'coupon_codes.csv';
//		$content    = $this->getLayout()->createBlock('aoecoupons/adminhtml_coupons_grid')
//			->getCsvFile();
//
//		$this->_prepareDownloadResponse($fileName, $content);
//	}

}