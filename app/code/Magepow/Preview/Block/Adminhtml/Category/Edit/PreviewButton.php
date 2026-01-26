<?php

namespace Magepow\Preview\Block\Adminhtml\Category\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;

/**
 * Class PreviewButton
 */
class PreviewButton extends AbstractCategory implements ButtonProviderInterface
{

    private function disablePreview()
    {
        return !$this->getCategory()->getIsActive()
            || $this->getCategory()->getLevel() < 2;
    }

    /**
     * Clear Cache button
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->disablePreview()) {
            return [];
        }
        $category = $this->getCategory();
        $previewCategory = clone $category;
        $storeId     = (int) $this->getRequest()->getParam('store');
        if ($storeId) {
            $storeUrl   = $previewCategory->getUrlInstance()->getBaseUrl(['_scope' => $storeId]);
            $storeUrl   = explode('/', $storeUrl);
            $previewUrl = $previewCategory->getUrl();
            $previewUrl = explode('/', $previewUrl);
            $previewUrl = array_unique(array_merge($storeUrl, $previewUrl));
            $previewUrl = implode('/', $previewUrl);
        } else {
            $storeId = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\Magento\Store\Model\StoreManagerInterface::class)
                        ->getDefaultStoreView()->getId();
            $previewCategory->setStoreId($storeId);
            $previewUrl = $previewCategory->getUrl();
        }

        return [
            'label' => __('Preview'),
            'class' => 'action-secondary preview',
            // 'on_click' => "confirmSetLocation('Are you sure', '{$previewUrl}')",
            'on_click' => 'window.open( \'' . $previewUrl . '\')',
            'sort_order' => 10
        ];
    }
}