<?php

namespace FourPaws\External\Export;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Result;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use InvalidArgumentException;
use PHPExcel;
use PHPExcel_IOFactory;
use Psr\Log\LoggerAwareTrait;

class ExcelExport
{
    const A = 65;

    private $docTitle;

    private $rowHeight;

    private $excludeCellWidth;

    use LazyLoggerAwareTrait;

    public function __construct($title = false)
    {
        if($title){
            $this->setDocTitle($title);
        }
    }

    /**
     * @param $headers
     * @param $rows
     * @throws \PHPExcel_Exception
     */
    public function generateExcel($headers, $rows)
    {
        if(empty($headers) || empty($rows)){
            throw new InvalidArgumentException(sprintf("parameter %s can't be empty", empty($headers) ? '$headers' : '$rows'));
        }

        $phpExcel = new PHPExcel();
        $phpExcel->setActiveSheetIndex(0);
        $page = $phpExcel->getActiveSheet();

        $letterCode = self::A;
        foreach ($headers as $header){
            $letter = strtoupper(chr($letterCode));
            $page->setCellValue($letter.'1', $header);
            $letterCode++;
        }

        $i = 2;
        foreach($rows as $row){
            $letterCode = self::A;

            foreach ($row as $cell){
                $letter = strtoupper(chr($letterCode));

                if(is_array($cell)){
                    if($cell['type'] == 'bitrix_img'){

                        if(!$cell['value']){
                            $this->log()->error('Картинка отсутствует');
                            continue;
                        }

                        $file = \CFile::GetById($cell['value'])->Fetch();
                        $url = $_SERVER['DOCUMENT_ROOT'].\CFile::GetPath($cell['value']);

                        if(!file_exists($url)){
                            $this->log()->error(sprintf('Файл %s не найден', $url));
                            continue;
                        }

                        $height = $file['HEIGHT'] < 100 ? $file['HEIGHT'] : 100;
                        $width = $file['WIDTH'] < 200 ? $file['WIDTH'] : 200;

                        $objDrawing = new \PHPExcel_Worksheet_Drawing();
                        $objDrawing->setName($file['ORIGINAL_NAME']);
                        $objDrawing->setDescription($file['DESCRIPTION']);
                        $objDrawing->setPath($url);
                        $objDrawing->setWidthAndHeight($width, $height);
                        $objDrawing->setCoordinates($letter.$i);
                        $objDrawing->setWorksheet($page);

                        $page->getRowDimension($i)
                            ->setRowHeight($objDrawing->getHeight());

                        $page->getColumnDimension($letter)->setWidth(30);
                        if(!in_array($letter, $this->excludeCellWidth)){
                            $this->excludeCellWidth[] = $letter;
                        }
                    }
                } else {
                    $value = $cell;
                    $page->setCellValue($letter.$i, $value);
                }


                $letterCode++;
            }
            $i++;
        }

        foreach(range(chr(self::A),chr($letterCode)) as $columnID) {
            if(in_array($columnID, $this->excludeCellWidth)){
                continue;
            }

            $phpExcel->getActiveSheet()
                ->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition:attachment;filename="'.$this->getDocTitle().'"');

        $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $objWriter->save('php://output');

        exit();
    }

    /**
     * @param mixed $docTitle
     * @return ExcelExport
     */
    public function setDocTitle($docTitle)
    {
        $this->docTitle = $docTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocTitle()
    {
        if(null === $this->docTitle){
            $this->docTitle = 'Новая_выгрузка_excel';
        }
        return sprintf('%s-%s.%s',  date('Y_m_d'), $this->docTitle, 'xls');
    }
}
