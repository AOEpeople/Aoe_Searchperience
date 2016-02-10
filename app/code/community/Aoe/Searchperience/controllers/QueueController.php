<?php

/**
 * Class Aoe_Searchperience_QueueController
 *
 * @category Controller
 * @package  Aoe_Searchperience
 * @author   AOE Magento Team <team-magento@aoe.com>
 * @license  none none
 * @link     www.aoe.com
 */
class Aoe_Searchperience_QueueController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Default action
     *
     * @return void
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
     *
     * @return void
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return  Mage::getSingleton('admin/session')->isAllowed('searchperience/queue');
    }
}
