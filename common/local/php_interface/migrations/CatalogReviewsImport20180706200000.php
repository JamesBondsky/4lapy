<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CatalogReviewsImport20180706200000 extends SprintMigrationBase
{
    protected $description = 'Импорт отзывов о товарах со старого сайта';
    protected $scvFilePath = '/local/php_interface/migration_sources/catalog_reviews.csv';
    protected $commentsHighloadBlockName = 'Comments';

    /**
     * @return bool
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function up()
    {
        $this->importReviews();

        return true;
    }
    
    public function down()
    {

    }

    /**
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function importReviews()
    {
        $filePathAbs = Application::getAbsolutePath($this->scvFilePath);

        $rs = fopen($filePathAbs, 'rb');
        if (!$rs) {
            throw new \RuntimeException(
                sprintf(
                    'Can not open file %s',
                    $filePathAbs
                )
            );
        }
        $rowsMap = [];
        $firstRow = true;
$a = 0;
$b = 0;
$c = 0;
        while ($row = fgetcsv($rs, null, ';', '"')) {
            if ($firstRow) {
                $firstRow = false;
                foreach ($row as $key => $fieldName) {
                    $rowsMap[trim($fieldName)] = $key;
                }
                continue;
            }

            $tmpKey = $rowsMap['DETAIL_TEXT'];
            $text = trim($row[$tmpKey]);
if ($text === '') {
    ++$a;
}
            $tmpKey = $rowsMap['DETAIL_TEXT_TYPE'];
            $textType = trim($row[$tmpKey]);
/**
 * @todo Уточнить, можно ли html передавать
*/
            if (mb_strtolower($textType) === 'html') {
                $text = HTMLToTxt($text, '', [], false);
            }

            // текст - обязательное поле

            if ($text === '') {
++$b;
                continue;
            }

            $tmpKey = $rowsMap['DATE_CREATE'];
            $dateCreate = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['ACTIVE'];
            $active = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROPERTY_RATING'];
            $rating = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROPERTY_USER'];
            $externalUserId = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROPERTY_REV_PRODUCT'];
            $externalProductOfferId = trim($row[$tmpKey]);

            $internalUserId = (int)$this->getInternalUserId($externalUserId);
            $internalProductOfferId = (int)$this->getInternalProductId($externalProductOfferId);
            $internalProductId = (int)$this->getProductIdByOfferId($internalProductOfferId);
/**
 * @todo Уточнить, нужно ли сохранять, если товар не найден
*/

            // в комментариях дата без времени
            $date = (new \DateTime($dateCreate))->format('d.m.Y');

            $newId = $this->addComment(
                [
                    'UF_DATE' => $date,
                    'UF_TYPE' => 'catalog',
                    'UF_OBJECT_ID' => $internalProductId,
                    'UF_ACTIVE' => $active === 'Y' ? 1 : 0,
                    'UF_MARK' => (int)$rating,
                    'UF_TEXT' =>  $text,
                    'UF_USER_ID' => (int)$internalUserId,
                ]
            );
if ($newId) {
    $c++;
}
        }

        fclose($rs);
//_log_array([$a, $b, $c], '$a');
    }

    /**
     * @param string $externalValue
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getInternalProductId(string $externalValue)
    {
        if ($externalValue !== '') {
            return \FourPaws\Migrator\Entity\MapTable::getInternalIdByExternalId($externalValue, 'catalog');
        }

        return '';
    }

    /**
     * @param string $externalValue
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getInternalUserId(string $externalValue)
    {
        if ($externalValue !== '') {
            return \FourPaws\Migrator\Entity\MapTable::getInternalIdByExternalId($externalValue, 'user');
        }

        return '';
    }

    /**
     * @return int
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function getProductOfferIBlockId()
    {
        static $iblockId = 0;
        if (!$iblockId) {
            $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        }

        return $iblockId;
    }

    /**
     * @param int $offerId
     * @return int
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function getProductIdByOfferId(int $offerId)
    {
        if ($offerId <= 0) {
            return 0;
        }
        $item = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $this->getProductOfferIBlockId(),
                'ID' => $offerId,
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_CML2_LINK'
            ]
        )->Fetch();

        return isset($item['PROPERTY_CML2_LINK_VALUE']) ? (int)$item['PROPERTY_CML2_LINK_VALUE'] : 0;
    }

    /**
     * @param array $fields
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    protected function addComment(array $fields)
    {
        $newId = 0;
        $dataManager = $this->getCommentsDataManager();
        // ищем комментарий по ключевым полям
/**
 * @todo Может быть ситуация, когда на один товар в одну дату пишет несколько анонимов - из таких комментов пройдет только один.
 * Отрубить нафиг проверку?
*/
        $item = $dataManager::getList(
            [
                'order' => [
                    'ID' => 'asc',
                ],
                'filter' => [
                    '=UF_USER_ID' => $fields['UF_USER_ID'],
                    '=UF_OBJECT_ID' => $fields['UF_OBJECT_ID'],
                    '=UF_TYPE' => $fields['UF_TYPE'],
                    '=UF_DATE' => $fields['UF_DATE'],
                    '=UF_MARK' => $fields['UF_MARK'],
                    // ?
                    '=UF_ACTIVE' => $fields['UF_ACTIVE'],
                ]
            ]
        )->fetch();
        $addResult = null;
        if (!$item) {
            $addResult = $dataManager::add($fields);
            if ($addResult->isSuccess()) {
                $newId = $addResult->getId();
            }
        } else {
//_log_array([$fields, $item], '$item');
        }

        return $newId;
    }

    /**
     * @return \Bitrix\Main\Entity\DataManager
     * @throws \Exception
     */
    protected function getCommentsDataManager()
    {
        static $dataManager = null;
        if (!$dataManager) {
            $dataManager = HLBlockFactory::createTableObject($this->commentsHighloadBlockName);
        }

        return $dataManager;
    }
}
