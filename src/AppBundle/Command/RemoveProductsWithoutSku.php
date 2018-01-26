<?php

namespace FourPaws\AppBundle\Command;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Exception;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BitrixClearUser
 *
 * @package FourPaws\AppBundle\Command
 */
class RemoveProductsWithoutSku extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    private $errorCount = 0;
    
    /**
     * BitrixClearHighloadBlock constructor.
     *
     * @param null $name
     *
     * @throws LogicException
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setLogger(new Logger('Remove_products', [new StreamHandler(STDOUT, Logger::DEBUG)]));
    }
    
    /**
     * @throws InvalidArgumentException
     */
    public function configure()
    {
        $this->setName('bitrix:catalog:remove_products_without_sku')
             ->setDescription('Remove products without sku from catalog');
    }
    
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null
     *
     * @throws InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->removeProducts();
            
            $this->logger->info(sprintf('Products has been delete.'));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unknown error: %s', $e->getMessage()));
        }
        
        return null;
    }
    
    /**
     * @throws IblockNotFoundException
     */
    private function removeProducts()
    {
        $subquery = \CIBlockElement::SubQuery('PROPERTY_CML2_LINK',
                                              [
                                                  'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG,
                                                                                          IblockCode::OFFERS),
                                              ]);
        
        $productCollection = \CIBlockElement::GetList([],
                                                      [
                                                          'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG,
                                                                                                  IblockCode::PRODUCTS),
                                                          '!ID'       => $subquery,
                                                      ],
                                                      false,
                                                      false,
                                                      ['ID']);
        
        $fullCount = $count = $productCollection->SelectedRowsCount();
        
        $this->logger->debug(sprintf('Products count - %s', $count));
        
        while ($product = $productCollection->Fetch()) {
            $this->removeProduct($product['ID']);
            $count--;
            
            if ($count % 100 === 0) {
                $this->logger->debug(sprintf('Products count - %s', $count--));
            }
        }
        
        $this->logger->debug(sprintf('Deleted - %d, errors - %d', $fullCount - $this->errorCount, $this->errorCount));
    }
    
    /**
     * @param int $id
     */
    private function removeProduct(int $id)
    {
        $cIBlockElement = new \CIBlockElement();
        
        if (!$cIBlockElement->Delete($id)) {
            $this->errorCount++;
            $this->logger->error(sprintf('Product with id %s remove error: %s', $id, $cIBlockElement->LAST_ERROR));
        }
    }
}
