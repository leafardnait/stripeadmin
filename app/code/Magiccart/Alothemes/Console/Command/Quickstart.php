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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;

class Quickstart extends Command
{

    /**
     * Date argument
     */
    const DATE_ARGUMENT = 'date';

    private $todayEndOfDayDate;
    private $todayStartOfDayDate;
    private $expiryDate;

    /**
     *
     * @var _objectManager
     */
    private $_objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $name
     */
    public function __construct(
        string $name = null,
        ?LoggerInterface $logger = null
    ) {

       $this->_objectManager = ObjectManager::getInstance();
       $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
       
       parent::__construct($name);
    }

    protected function configure()
    {
        // command: bin/magento initQuickstart
        $this->setName('initQuickstart')
             ->setDescription('Config init quickstart');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $date = new \DateTime();
        // $date = new DateTime('2025-01-01');
        $this->todayStartOfDayDate = $date->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $this->todayEndOfDayDate = $date->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $today = \date('Y-m-d');
        $this->expiryDate = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($today)) );

        $returnValue = Cli::RETURN_SUCCESS;
        try {
            $model = $this->_objectManager->get('Magento\Catalog\Model\Product\Action');
            $newProducts = $this->getNewProducts();
            $num = 0;
            foreach ($newProducts as $product) {
                // $product->setStoreId(0)->setData('news_to_date', $setTime)->save();
                $model->updateAttributes([$product->getId()], ['news_to_date' => $this->expiryDate], 0);     
                $num++;
            }

            $message = __("Successfully updated %1 New product(s)!", $num);
            $output->writeln($message);

            $saleProducts = $this->getSaleProducts();
            $num = 0;
            foreach ($saleProducts as $product) {
                // $product->setStoreId(0)->setData('special_to_date', $setTime)->save();
                $model->updateAttributes([$product->getId()], ['special_to_date' => $this->expiryDate], 0);     
                $num++;
            }

            $message = __("Successfully updated %1 Sale product(s)!", $num);
            $output->writeln($message);

        } catch (IOExceptionInterface $e) {
            $message = __("An error occurred while deleting your directory at %1", $e->getPath());
            $output->writeln($message);
            $output->writeln($e->getMessage());
            $returnValue = Cli::RETURN_FAILURE;

            $this->logger->critical($e->getMessage());
        }

        return $returnValue;
    }

    public function getNewProducts() {

        // $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        // $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->todayEndOfDayDate;
        $todayStartOfDayDate = $this->todayStartOfDayDate;
        $collection = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

        $collection->addStoreFilter()
        ->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )
        // ->addAttributeToFilter(
        //     'news_to_date',
        //     [
        //         'or' => [
        //             0 => ['date' => true, 'from' => $todayStartOfDayDate],
        //             1 => ['is' => new \Zend_Db_Expr('null')],
        //         ]
        //     ],
        //     'left'
        // )
        ->addAttributeToFilter(
            [
                ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort('news_from_date', 'desc');

        return $collection;
    }

    public function getSaleProducts(){

        // $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        // $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->todayEndOfDayDate;
        $todayStartOfDayDate = $this->todayStartOfDayDate;
        $collection = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

        $collection->addStoreFilter()
        ->addAttributeToFilter(
            'special_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )
        // ->addAttributeToFilter(
        //     'special_to_date',
        //     [
        //         'or' => [
        //             0 => ['date' => true, 'from' => $todayStartOfDayDate],
        //             1 => ['is' => new \Zend_Db_Expr('null')],
        //         ]
        //     ],
        //     'left'
        // )
        ->addAttributeToFilter(
            [
                ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort('special_to_date', 'desc');

        return $collection;

    }

}