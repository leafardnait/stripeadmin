<?php

namespace Magepow\InfiniteScroll\Block;

/**
 * Class InfiniteScroll
 *
 */
class InfiniteScroll extends \Magento\Framework\View\Element\Template
{

   /**
     * InfiniteScroll constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }


    /**
     * getLoadingImage
     *
     * @return mixed
     */
    public function getMedia($img=null)
    {
        $urlMedia = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if($img) return $urlMedia . $img;
        return $urlMedia;
    }

}