<?php

namespace FourPaws\Console\Command;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OffersExport
 * CSV-экспорт торговых предложений
 *
 * @package FourPaws\Console\Command
 */
class OffersExport extends Command
{
    use LazyLoggerAwareTrait;

    protected $offersIBlockId = 0;
    protected $csvExportFilePath = '';
    protected $docRoot = '';
    protected $csvExporter = null;

    public function __construct($name = null)
    {
        parent::__construct($name);

        // ID инфоблока
        $this->offersIBlockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        $this->docRoot = \Bitrix\Main\Application::getDocumentRoot();
    }
    
    protected function configure()
    {
        $this->setName('fourpaws:offers:export');
        $this->setDescription('Выгрузка данных из инфоблока торговых предложений');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Экспорт начат: '.date('Y-m-d H:i:s'));
        try {
            $csvExportFilePath = $this->doCsvExport();
            $output->writeln('Результат выгрузки: '.$csvExportFilePath);
            $output->writeln('Экспорт завершен: '.date('Y-m-d H:i:s'));
        } catch (\Exception $exception) {
            $output->writeln('Произошла ошибка: '.$exception->getMessage());

            $this->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }

    }

    /**
     * @return \CCSVData
     */
    protected function getCsvExporter()
    {
        if (!$this->csvExporter) {
            require_once($this->docRoot.'/bitrix/modules/main/classes/general/csv_data.php');
            $this->csvExporter = new \CCSVData();
            $this->csvExporter->SetFieldsType('R');
            $this->csvExporter->SetDelimiter(';');
        }

        return $this->csvExporter;
    }

    /**
     * @return string
     */
    protected function getCsvExportFilePath()
    {
        // путь, куда будет выгружен файл
        if (!$this->csvExportFilePath) {
            $this->csvExportFilePath = '/upload/offers_export-'.date('dmY-His').'.csv';
        }

        return $this->csvExportFilePath;
    }

    /**
     * @return string
     */
    protected function doCsvExport()
    {
        $csvExportFilePath = $this->getCsvExportFilePath();
        $csvExportFilePathFull = $this->docRoot.$csvExportFilePath;

        $csvExporter = $this->getCsvExporter();

        // первая строка с названиями полей
        $rawData = [
            'NAME',
            'XML_ID',
            'CML2_LINK_ID',
            'CML2_LINK_NAME',
            'ACTIVE',
        ];
        $csvExporter->SaveFile($csvExportFilePathFull, $rawData);

        // тороговые предложения
        $items = \CIBlockElement::GetList(
            [
                'ID' => 'ASC'
            ],
            [
                'IBLOCK_ID' => $this->offersIBlockId,
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'ACTIVE',
                'XML_ID',
                'PROPERTY_CML2_LINK',
                'PROPERTY_CML2_LINK.NAME',
            ]
        );
        while ($item = $items->Fetch()) {
            $rawData = [
                utf8win1251($item['NAME']),
                utf8win1251($item['XML_ID']),
                $item['PROPERTY_CML2_LINK_VALUE'],
                utf8win1251($item['PROPERTY_CML2_LINK_NAME']),
                $item['ACTIVE'],
            ];

            $csvExporter->SaveFile($csvExportFilePathFull, $rawData);
        }

        return $csvExportFilePath;
    }
}
