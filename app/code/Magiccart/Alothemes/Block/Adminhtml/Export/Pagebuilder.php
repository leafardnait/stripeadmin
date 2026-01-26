<?php

/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2020-04-06 21:40:25
 * @@Function:
 */

namespace Magiccart\Alothemes\Block\Adminhtml\Export;

use Magento\Theme\Model\Theme\Collection;
use Magento\Framework\App\Area;

use Magiccart\Alothemes\Model\Status;

class Pagebuilder extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('PagebuilderGrid');
        $this->setDefaultSort('template_id');
        $this->setDefaultDir('ASC');
        $this->setDefaultLimit(100);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'template_id',
            [
                'header' => __('Template ID'),
                'index' => 'template_id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'preview_image',
            [
                'header' => __('Preview Image'),
                'class' => 'xxx',
                'width' => '50px',
                'filter' => false,
                'renderer' => 'Magiccart\Alothemes\Block\Adminhtml\Export\Helper\Pagebuilder\Grid\Image',
            ]
        );

        $this->addColumn(
            'creation_time',
            [
                'header' => __('Created'),
                'index' => 'created_at',
                'type' => 'datetime',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

        $this->addColumn(
            'update_time',
            [
                'header' => __('Updated'),
                'index' => 'updated_at',
                'type' => 'datetime',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * After load collection
     *
     * @return void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['template_id' => $row->getId()]);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('template_id');
        $this->getMassactionBlock()->setFormFieldName('exportIds');

        $theme = \Magento\Framework\App\ObjectManager::getInstance()->create('Magiccart\Alothemes\Model\Export\Theme');
        $themes = $theme->toOptionArray();

        $this->getMassactionBlock()->addItem('export', array(
            'label'    => __('Export'),
            'url'      => $this->getUrl('*/*/pagebuilder'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'theme_path',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Theme'),
                    'values' => $themes //$stores
                )
            ),
            'confirm'  => __('Are you sure?')
        ));
        return $this;
    }

    public function toHtml()
    {
        $html = parent::toHtml();
        /*
        $find = $this->escapeHtmlAttr("alothemes/export");
        $replace = $this->escapeHtmlAttr("cms/block");
        $html = str_replace($find, $replace, $html);
        */
        $html =  $this->removeUrl($html);

        return $html;
    }

    public function removeUrl($html)
    {
        $pattern = '/<tr([\s\S]*?)(?:title="(.*?)")([\s\S]*?)?([^>]*)>/';
        return preg_replace_callback(
            $pattern,
            function ($match) {
                return isset($match[2]) ? str_replace($match[2], '#', (string) $match[0]) : $match[0];
            },
            $html
        );
    }
}