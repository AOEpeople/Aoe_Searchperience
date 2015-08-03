<?php

/**
 * Sitemap edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Aoe_Searchperience_Block_Adminhtml_Sitemap_Edit_Form extends Mage_Adminhtml_Block_Sitemap_Edit_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('sitemap_sitemap');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('add_sitemap_form', array('legend' => Mage::helper('sitemap')->__('Sitemap')));

        if ($model->getId()) {
            $fieldset->addField('sitemap_id', 'hidden', array(
                'name' => 'sitemap_id',
            ));
        }

        $fieldset->addField('sitemap_filename', 'text', array(
            'label' => Mage::helper('sitemap')->__('Filename'),
            'name'  => 'sitemap_filename',
            'required' => true,
            'note'  => Mage::helper('adminhtml')->__('example: sitemap_[STORE_VIEW_CODE].xml'),
            'value' => $model->getSitemapFilename()
        ));

        $fieldset->addField('sitemap_cms_only', 'select', array(
            'label'     => Mage::helper('sitemap')->__('CMS Pages only'),
            'name'      => 'sitemap_cms_only',
            'value'     => '1',
            'values'    => array(
                '-1' => Mage::helper('sitemap')->__('Please Select'),
                '1'  => Mage::helper('sitemap')->__('Yes'),
                '0'  => Mage::helper('sitemap')->__('No')
            ),
            'disabled'  => false,
            'readonly'  => false,
        ));

        $fieldset->addField('sitemap_path', 'text', array(
            'label' => Mage::helper('sitemap')->__('Path'),
            'name'  => 'sitemap_path',
            'required' => true,
            'note'  => Mage::helper('adminhtml')->__('example: "sitemap/" or "/" for base path (path must be writeable)'),
            'value' => $model->getSitemapPath()
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'select', array(
                'label'    => Mage::helper('sitemap')->__('Store View'),
                'title'    => Mage::helper('sitemap')->__('Store View'),
                'name'     => 'store_id',
                'required' => true,
                'value'    => $model->getStoreId(),
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'     => 'store_id',
                'value'    => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('generate', 'hidden', array(
            'name'     => 'generate',
            'value'    => ''
        ));

        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return $this;
    }

}
