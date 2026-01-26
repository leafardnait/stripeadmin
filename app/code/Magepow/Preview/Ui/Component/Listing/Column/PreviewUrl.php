<?php

namespace Magepow\Preview\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;

class PreviewUrl extends Column
{

    private function disablePreview($item)
    {
        return $item['status'] == Status::STATUS_DISABLED
            || $item['visibility'] == Visibility::VISIBILITY_NOT_VISIBLE;
    }

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id']) && !$this->disablePreview($item)) {
                    $name = $this->getData('name');
                    $productId = $item['entity_id'];
                    $storeId   = (int) $this->getContext()->getRequestParam('store');
                    if (!$storeId) {
                        // $storeId = $this->storeManager->getStore()->getId();
                        $storeId = $this->storeManager->getDefaultStoreView()->getId();
                    }

                    $product   = $this->productRepository->getById($productId, false, $storeId);
                    $productURL = $product->setStoreId($storeId)->getUrlModel()->getUrlInStore($product, ['_escape' => true]);
                    $item[$name] = html_entity_decode('<a class="product_preview_' . $productId . '" href="' . $productURL . '">' . __('Preview') . '</a><script>document.querySelector(".product_preview_' . $productId . '").addEventListener("click", function (e) {e.stopPropagation();});</script>');
                }
            }
        }
        return $dataSource;
    }
}