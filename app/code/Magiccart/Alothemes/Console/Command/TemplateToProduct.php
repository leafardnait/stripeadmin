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
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;

class TemplateToProduct extends Command
{

    /**
     * Attribute argument
     */
    const ATTRIBUTE_ARGUMENT = 'attribute';

    /**
     * Sku argument
     */
    const SKU_ARGUMENT = 'sku';

    /**
     * Template argument
     */
    const TEMPLATE_ARGUMENT = 'template';

    /**
     * Allow option
     */
    const ALL_PRODUCT = 'all-product';

    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

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
     * @param TemplateRepositoryInterface $templateRepository
     * @param CollectionFactory $collectionFactory
     * @param Visibility $collectionFactory
     * @param string $name
     */
    public function __construct(
        TemplateRepository $templateRepository,
        CollectionFactory $collectionFactory,
        Visibility $visibility,
        string $name = null,
        ?LoggerInterface $logger = null
    ) {
        $this->templateRepository = $templateRepository;
        $this->collectionFactory  = $collectionFactory;
        $this->visibility = $visibility;
        $this->objectManager = ObjectManager::getInstance();
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);

        parent::__construct($name);
    }

    protected function configure()
    {
        // command: php bin/magento TemplateToProduct description 24-MB02-1 23
        // command: php bin/magento TemplateToProduct description '*' 23
        $this->setName('TemplateToProduct')
            ->setDescription('Add a Template to product attribute')
            ->setDefinition([
                new InputArgument(
                    self::ATTRIBUTE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Attribute of Product'
                ),
                new InputArgument(
                    self::SKU_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Sku of Product'
                ),
                new InputArgument(
                    self::TEMPLATE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Template Id of PageBuilder: Admin Panel > Content > Templates'
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

        $returnValue = Cli::RETURN_SUCCESS;
        try {
            $attribute = $input->getArgument(self::ATTRIBUTE_ARGUMENT);
            if ($attribute) $attribute = 'description';
            $sku = $input->getArgument(self::SKU_ARGUMENT);
            $templateId = $input->getArgument(self::TEMPLATE_ARGUMENT);
            $allProduct = $input->getOption(self::ALL_PRODUCT);
            $template = $this->getTemplate($templateId);
            $model = $this->objectManager->get('Magento\Catalog\Model\Product\Action');
            $products = $this->getProducts($sku, $allProduct);
            $num = 0;
            foreach ($products as $product) {
                $productId = $product->getId();
                $output->writeln($product->getName());
                $model->updateAttributes([$productId], [$attribute => $template->getTemplate()], 0);
                $num++;
            }

            $message = __("Successfully updated %1 %2 product(s)!", $num, $attribute);
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
            $collection->addAttributeToFilter('sku', $skuFilter);
        } else if(!$allProduct){
            $collection->setVisibility($this->visibility->getVisibleInCatalogIds());
        }

        return $collection;
    }

    public function getTemplate($templateId)
    {
        $template = $this->templateRepository->get($templateId);

        return $template;
    }
}