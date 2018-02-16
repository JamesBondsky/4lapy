<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\CatalogBundle\Console;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportDescriptionFromCsv extends Command implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var \CIBlockElement
     */
    private $cIblockElement;

    /**
     * @var \Bitrix\Main\DB\Connection
     */
    private $connect;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->cIblockElement = new \CIBlockElement();
        $this->connect = Application::getConnection();
    }

    public function configure()
    {
        $this
            ->setName('bitrix:product:description:import')
            ->setDescription('Import detail_text, composition, norms of use from csv file for existed products by offer xml_id')
            ->addArgument('path', InputArgument::REQUIRED, 'Full path to csv file');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        if ($path && is_file($path) && is_readable($path)) {
            $progress = new ProgressBar($output, 100);
            $progress->start();

            $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);


            $fileSize = filesize($path);

            $total = 0;
            $notFound = 0;
            $updatedProductId = [];

            $handler = fopen($path, 'rb+');
            /**
             * skip header
             */
            fgetcsv($handler);
            while ($data = fgetcsv($handler)) {
                $total++;
                if ($total % 500 === 0 && ($point = ftell($handler))) {
                    $progress->setProgress($this->countPercent($fileSize, $point));
                }
                $data = array_map(function ($element) {
                    $element = $element ?: '';
                    return trim($element);
                }, $data);
                list($xmlId, $name, $complectName, $detailText, $composition, $normsOfUse, $code) = $data;
                if (!$xmlId) {
                    $this->log()->error('No xml id was passed');
                    continue;
                }
                $productId = $this->findProductId($xmlId);
                if (!$productId) {
                    $notFound++;
                    $this->log()->error(sprintf('Cant find product for %s xml id', $xmlId));
                    continue;
                }

                if (isset($updatedProductId[$productId])) {
                    $this->log()->critical(sprintf(
                        'Product %s already updated for xml id %s',
                        $productId,
                        $updatedProductId[$productId]
                    ));
                }
                try {
                    $this->connect->startTransaction();

                    $fields = [
                        'NAME'        => $complectName ?: $name,
                        'IBLOCK_ID'   => $iblockId,
                        'DETAIL_TEXT' => $detailText,
                    ];
                    if ($code) {
                        $fields['CODE'] = $code;
                    }

                    $updateResult = $this->cIblockElement->Update($productId, $fields);
                    if (!$updateResult) {
                        $this->log()->error(sprintf(
                            'Product %s update error: %s',
                            $productId,
                            $this->cIblockElement->LAST_ERROR
                        ));
                        $this->cIblockElement->LAST_ERROR = '';
                        continue;
                    }

                    \CIBlockElement::SetPropertyValuesEx($productId, $iblockId, [
                        'COMPOSITION'  => $composition,
                        'NORMS_OF_USE' => $normsOfUse,
                    ]);
                    $updatedProductId[$productId] = $xmlId;
//                    $this->log()->debug(sprintf('Updated %s product with offer xml id %s', $productId, $xmlId));
                    $this->connect->commitTransaction();
                } catch (\Exception $exception) {
                    $this->connect->rollbackTransaction();
                }
            }

            $this->log()->debug(sprintf('Updated: %s', \count($updatedProductId)));
            $this->log()->debug(sprintf('Not found: %s', $notFound));
            $this->log()->debug(sprintf('Error: %s', $total - \count($updatedProductId) - $notFound));
        }
    }

    protected function countPercent(int $total, int $current)
    {
        return floor($current / ($total / 100));
    }

    protected function findProductId(string $offerXmlId)
    {
        $filter = [
            'IBLOCK_ID'           => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
            'XML_ID'              => $offerXmlId,
            '!PROPERTY_CML2_LINK' => false,
        ];

        $select = [
            'PROPERTY_CML2_LINK',
        ];

        $product = \CIBlockElement::GetList([], $filter, false, ['nTopCount' => 1], $select)->Fetch();
        return $product['PROPERTY_CML2_LINK_VALUE'] ?? null;
    }
}
