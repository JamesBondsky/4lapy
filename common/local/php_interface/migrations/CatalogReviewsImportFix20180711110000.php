<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Migrator\Entity\MapTable;

class CatalogReviewsImportFix20180711110000 extends SprintMigrationBase
{
    protected $description = 'Импорт отзывов о товарах со старого сайта с исправлениями';
    protected $scvFilePath = '/local/php_interface/migration_sources/catalog_reviews_fix.csv';
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
        $this->createFields();
        $this->importReviews();

        return true;
    }
    
    public function down()
    {

    }

    protected function createFields()
    {
        $helper = new HelperManager();

        $hlblockId = $helper->Hlblock()->getHlblockId(
            $this->commentsHighloadBlockName
        );
        $entityId  = 'HLBLOCK_' . $hlblockId;

        $fieldCode = 'UF_XML_ID';
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists(
            $entityId,
            $fieldCode,
            [
                'FIELD_NAME' => $fieldCode,
                'USER_TYPE_ID' => 'string',
                'XML_ID' => $fieldCode,
                'SORT' => '1000',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'I',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => [
                    'SIZE' => 10,
                    'ROWS' => 0,
                    'REGEXP' => '',
                    'MIN_LENGTH' => 0,
                    'MAX_LENGTH' => 0,
                    'DEFAULT_VALUE' => '',
                ],
                'EDIT_FORM_LABEL' => [
                    'ru' => 'XML_ID',
                ],
                'LIST_COLUMN_LABEL' => [
                    'ru' => 'XML_ID',
                ],
                'LIST_FILTER_LABEL' => [
                    'ru' => 'XML_ID',
                ],
                'ERROR_MESSAGE' => [
                    'ru' => '',
                ],
                'HELP_MESSAGE' => [
                    'ru' => 'XML_ID',
                ],
            ]
        );
    }

    /**
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    protected function importReviews()
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
            $tmpKey = $rowsMap['DETAIL_TEXT_TYPE'];
            $textType = trim($row[$tmpKey]);
            if (mb_strtolower($textType) === 'html') {
                $text = HTMLToTxt($text, '', [], false);
            }

            // текст - обязательное поле

            if ($text === '') {
                continue;
            }

            $tmpKey = $rowsMap['ID'];
            $xmlId = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['DATE_CREATE'];
            $dateCreate = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['ACTIVE'];
            $active = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROPERTY_RATING'];
            $rating = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROPERTY_USER'];
            $externalUserId = trim($row[$tmpKey]);

            $tmpKey = $rowsMap['PROPERTY_REV_PRODUCT_XML_ID'];
            $externalProductOfferXmlId = trim($row[$tmpKey]);

            $internalProductData = $this->getInternalProductDataByXmlId($externalProductOfferXmlId);
            $internalProductId = $internalProductData ? $internalProductData['PRODUCT_ID'] : 0;
            // не сохраняем отзывы без связи с товаром
            if (!$internalProductId) {
                continue;
            }

            $internalUserId = (int)$this->getInternalUserId($externalUserId);

            // в комментариях дата без времени
            $date = (new \DateTime($dateCreate))->format('d.m.Y');

            $this->addComment(
                [
                    'UF_XML_ID' => $xmlId,
                    'UF_DATE' => $date,
                    'UF_TYPE' => 'catalog',
                    'UF_OBJECT_ID' => $internalProductId,
                    'UF_ACTIVE' => $active === 'Y' ? 1 : 0,
                    'UF_MARK' => (int)$rating,
                    'UF_TEXT' =>  $text,
                    'UF_USER_ID' => (int)$internalUserId,
                ]
            );
        }

        fclose($rs);
    }

    /**
     * @param string $externalValue
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function getInternalProductDataByXmlId(string $externalValue)
    {
        $data = [];
        if ($externalValue !== '') {
            $item = \CIBlockElement::GetList(
                [
                    'ID' => 'ASC',
                ],
                [
                    'IBLOCK_ID' => $this->getProductOfferIBlockId(),
                    '=XML_ID' => $externalValue
                ],
                false,
                false,
                [
                    'ID',
                    'PROPERTY_CML2_LINK'
                ]
            )->Fetch();
            if ($item) {
                $data = [
                    'OFFER_ID' => (int)$item['ID'],
                    'PRODUCT_ID' => (int)$item['PROPERTY_CML2_LINK_VALUE'],
                ];
            }
        }

        return $data;
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
            return MapTable::getInternalIdByExternalId($externalValue, 'user');
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
        // ищем комментарий
        $item = $dataManager::getList(
            [
                'order' => [
                    'ID' => 'asc',
                ],
                'filter' => [
                    '=UF_XML_ID' => $fields['UF_XML_ID'],
                ]
            ]
        )->fetch();
        if (!$item) {
            $addResult = $dataManager::add($fields);
            if ($addResult->isSuccess()) {
                $newId = $addResult->getId();
            }
        } else {
            $addResult = $dataManager::update($item['ID'], $fields);
            if ($addResult->isSuccess()) {
                $newId = $addResult->getId();
            }
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
