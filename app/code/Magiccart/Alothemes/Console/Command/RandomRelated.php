<?php

/**
 * @Author: nguyen
 * @Date:   2020-12-15 14:01:01
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2023-08-19 16:25:12
 * https://github.com/magento/magento2-samples/tree/master/sample-module-command/Console/Command
 */

namespace Magiccart\Alothemes\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Magento\PageBuilder\Api\TemplateRepositoryInterface;
use Magento\PageBuilder\Model\TemplateRepository;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

class RandomRelated extends Command
{
    /**
     * Sku argument
     */
    const SKU_ARGUMENT = 'sku';

    /**
     * LIMIT argument
     */
    const LIMIT_ARGUMENT = 'limit';

    /**
     * Allow option
     */
    const ALL_PRODUCT = 'all-product';

    protected $linkType = 'related';

    protected $commandName = 'RandomRelated';

    /** @var \Magento\Framework\App\State **/
    private $state;

    /**
     * @var ProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $visibility;

    /**
     *
     * @var objectManager
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductLinkInterfaceFactory $productLinkFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $collectionFactory
     * @param Visibility $collectionFactory
     * @param string $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        ProductLinkInterfaceFactory $productLinkFactory,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        Visibility $visibility,
        string $name = null,
        ?LoggerInterface $logger = null
    ) {
        $this->state = $state;
        $this->productLinkFactory = $productLinkFactory;
        $this->productRepository  = $productRepository;
        $this->collectionFactory  = $collectionFactory;
        $this->visibility = $visibility;
        $this->objectManager = ObjectManager::getInstance();
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);

        parent::__construct($name);
    }

    protected function configure()
    {
        // command: php bin/magento RandomRelated 24-MB02-1 10
        // command: php bin/magento RandomRelated '*' 10
        $this->setName($this->commandName)
            ->setDescription(__('Add a Random %1 product', $this->linkType))
            ->setDefinition([
                new InputArgument(
                    self::SKU_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Sku of Product'
                ),
                new InputArgument(
                    self::LIMIT_ARGUMENT,
                    InputArgument::OPTIONAL,
                    __('Number %1 product will add', $this->linkType)
                ),
                new InputOption(
                    self::ALL_PRODUCT,
                    '-a',
                    InputOption::VALUE_NONE,
                    'All Products includes product not Visibility'
                ),

            ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML); 
        $returnValue = Cli::RETURN_SUCCESS;
        try {
            $sku   = $input->getArgument(self::SKU_ARGUMENT);
            $limit = $input->getArgument(self::LIMIT_ARGUMENT);
            $allProduct = $input->getOption(self::ALL_PRODUCT);
            $products = $this->getProducts($sku, $allProduct);
            $productSkus = [];
            foreach ($products as $product) {
                $productSkus[$product->getSku()] = $product->getId();
            }
            $num = 0;
            foreach ($productSkus as $mainSku => $id) {
                $mainProduct = $this->productRepository->get($mainSku);
                $tmp = $productSkus;
                unset($tmp[$mainSku]);
                $relatedProducts = [];
                $relatedSku = array_rand($tmp, $limit);
                foreach ($relatedSku as $linkSku) {
                    $relatedProducts[] = $this->productLinkFactory->create()
                                            ->setSku($mainSku)
                                            ->setLinkedProductSku($linkSku)
                                            ->setLinkType($this->linkType);
                }
                $mainProduct->setProductLinks($relatedProducts)->save();
                $output->writeln($mainProduct->getName());
                $num++;
            }           

            $message = __("Successfully add %1 %2 product(s)!", $this->linkType, $num);
            $output->writeln($message);

            $arguments = new ArrayInput(['command' => 'cache:flush']);
            $this->getApplication()->find('cache:flush')->run($arguments, $output);
        } catch (IOExceptionInterface $e) {
            $message = __("An error occurred while deleting your directory at %1", $e->getPath());
            $output->writeln($message);
            $output->writeln($e->getMessage());
            $returnValue = Cli::RETURN_FAILURE;

            $this->logger->critical($e->getMessage());
        }

        return $returnValue;
    }

    public function getProducts($skuFilter, $allProduct=false)
    {
        if ($skuFilter == '*') {
            $skuFilter = '';
        } elseif (str_contains($skuFilter, '%')) {
            $skuFilter = ['like' => $skuFilter];
        } elseif (str_contains($skuFilter, ',')) {
            $skuFilter = explode(',', $skuFilter);
        }
        $collection = $this->collectionFactory->create()->setStoreId(0)->addAttributeToSelect('name');

        if ($skuFilter) {
            $collection->addAttributeToSelect('sku')
                        ->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
                        ->addAttributeToFilter('sku', $skuFilter);
        } else if(!$allProduct){
            $collection->addAttributeToFilter('status', ProductStatus::STATUS_ENABLED)
                    ->setVisibility($this->visibility->getVisibleInCatalogIds());
        }

        return $collection;
    }
}