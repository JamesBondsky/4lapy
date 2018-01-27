<?php

namespace FourPaws\AppBundle\Command;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveProductsWithoutSku
 *
 * @package FourPaws\AppBundle\Command
 */
class RemoveProductsWithoutSku extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private $errorCount = 0;

    /**
     * @var \CIBlockElement
     */
    private $cIblockElement;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->cIblockElement = new \CIBlockElement();
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
     * @throws InvalidArgumentException
     * @return null
     *
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->removeProducts();

            $this->log()->info(sprintf('Products has been delete.'));
        } catch (\Exception $e) {
            $this->log()->error(sprintf('Unknown error: %s', $e->getMessage()));
        }

        return null;
    }

    /**
     * @throws IblockNotFoundException
     */
    private function removeProducts()
    {
        $subquery = \CIBlockElement::SubQuery(
            'PROPERTY_CML2_LINK',
            [
                'IBLOCK_ID' => IblockUtils::getIblockId(
                    IblockType::CATALOG,
                    IblockCode::OFFERS
                ),
            ]
        );

        $productCollection = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => IblockUtils::getIblockId(
                    IblockType::CATALOG,
                    IblockCode::PRODUCTS
                ),
                '!ID'       => $subquery,
            ],
            false,
            false,
            ['ID']
        );

        $fullCount = $count = $productCollection->SelectedRowsCount();

        $this->log()->debug(sprintf('Products count - %s', $count));

        while ($product = $productCollection->Fetch()) {
            $this->removeProduct($product['ID']);
            $count--;

            if ($count % 100 === 0) {
                $this->log()->debug(sprintf('Products count - %s', $count--));
            }
        }

        $this->log()->debug(sprintf('Deleted - %d, errors - %d', $fullCount - $this->errorCount, $this->errorCount));
    }

    /**
     * @param int $id
     */
    private function removeProduct(int $id)
    {
        if (!$this->cIblockElement::Delete($id)) {
            $this->errorCount++;
            $this->log()->error(sprintf('Product with id %s remove error: %s', $id, $this->cIblockElement->LAST_ERROR));
        }
    }
}
