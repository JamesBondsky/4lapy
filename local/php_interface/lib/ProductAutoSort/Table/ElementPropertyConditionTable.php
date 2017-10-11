<?php

namespace FourPaws\ProductAutoSort\Table;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Exception;
use FourPaws\ProductAutoSort\UserType\ElementPropertyConditionUserType;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ElementPropertyConditionTable extends DataManager
{
    /**
     * @var LoggerInterface
     */
    protected static $logger;

    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return '4lp_elem_prop_cond';
    }

    /**
     * @return array
     */
    public static function getMap()
    {
        return [
            'ID'             => new IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),

            //ID пользовательского свойства
            'UF_ID'          => new IntegerField('UF_ID', ['required' => true]),

            //ID раздела, в котором используется кастомное свойство "Условие для свойств элемента"
            'SECTION_ID'     => new IntegerField('SECTION_ID', ['required' => true]),

            //ID свойства элемента, которое надо проверить.
            'PROPERTY_ID'    => new IntegerField('PROPERTY_ID', ['required' => true]),

            //Значение свойства. Если null - символизирует незаполненное свойство.
            'PROPERTY_VALUE' => new StringField('PROPERTY_VALUE', ['default_value' => null, 'size' => 255]),
        ];
    }

    /**
     * Синхронизирует множество значений для условия свойства элемента
     *
     * @param int $ufId
     * @param int $sectionId
     * @param array $valueList
     */
    public static function syncRowMulti(int $ufId, int $sectionId, array $valueList)
    {
        try {
            /**
             * Удалить все записи для данного ufId + sectionId, чтобы не пытаться отловить удалённые значения.
             */
            $dbExistingItemList = self::query()
                                      ->setSelect(['ID'])
                                      ->setFilter(
                                          [
                                              '=UF_ID'      => $ufId,
                                              '=SECTION_ID' => $sectionId,
                                          ]
                                      )
                                      ->exec();
            while ($existingItem = $dbExistingItemList->fetch()) {

                $result = self::delete($existingItem['ID']);

                if (!$result->isSuccess()) {
                    self::log()->error(
                        sprintf(
                            'Ошибка удаления условия для свойства: %s',
                            BitrixUtils::extractErrorMessage($result)
                        ),
                        [
                            'existingItem' => $existingItem,
                        ]
                    );

                }

            }

            /**
             * Создать отдельные значения
             */
            foreach ($valueList as $serializedValue) {

                $value = unserialize($serializedValue);
                if (!is_array($value)) {
                    continue;
                }

                $newItem = [
                    'UF_ID'          => $ufId,
                    'SECTION_ID'     => $sectionId,
                    'PROPERTY_ID'    => (int)$value[ElementPropertyConditionUserType::VALUE_PROP_ID],
                    'PROPERTY_VALUE' => $value[ElementPropertyConditionUserType::VALUE_PROP_VALUE],
                ];

                $result = self::add($newItem);
                if (!$result->isSuccess()) {
                    throw new RuntimeException(
                        sprintf(
                            'Ошибка синхронизации условия для свойства: %s',
                            BitrixUtils::extractErrorMessage($result)
                        )
                    );
                }
            }

        } catch (Exception $exception) {

            if (!isset($existingItem)) {
                $existingItem = null;
            }

            self::log()->error(
                sprintf(
                    "[%s] %s (%s)\n%s\n",
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getTraceAsString()
                ),
                [
                    'existingItem' => $existingItem,
                ]
            );

        }
    }

    /**
     * Синхронизирует значение для условия свойства элемента
     *
     * @param int $ufId
     * @param int $sectionId
     * @param int $propertyId
     * @param $value
     */
    public static function syncRow(int $ufId, int $sectionId, int $propertyId, $value)
    {
        try {

            $existingItem = self::query()
                                ->setSelect(['ID'])
                                ->setFilter(
                                    [
                                        '=UF_ID'       => $ufId,
                                        '=SECTION_ID'  => $sectionId,
                                        '=PROPERTY_ID' => $propertyId,
                                    ]
                                )
                                ->exec()
                                ->fetch();

            /**
             * Пустая строка расценивается как незаполненное свойство
             */
            if (trim($value) == '') {
                $value = null;
            }

            $newItem = [
                'UF_ID'          => $ufId,
                'SECTION_ID'     => $sectionId,
                'PROPERTY_ID'    => $propertyId,
                'PROPERTY_VALUE' => $value,
            ];

            if (false == $existingItem) {
                $result = self::add($newItem);
            } else {
                $result = self::update($existingItem['ID'], $newItem);
            }

            if (!$result->isSuccess()) {
                throw new RuntimeException(
                    sprintf(
                        'Ошибка синхронизации условия для свойства: %s',
                        BitrixUtils::extractErrorMessage($result)
                    )
                );
            }

        } catch (Exception $exception) {

            if (!isset($existingItem)) {
                $existingItem = null;
            }

            if (!isset($newItem)) {
                $newItem = null;
            }

            self::log()->error(
                sprintf(
                    "[%s] %s (%s)\n%s\n",
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getTraceAsString()
                ),
                [
                    'existingItem' => $existingItem,
                    'newItem'      => $newItem,
                ]
            );

        }

    }

    /**
     * @return LoggerInterface
     */
    protected static function log()
    {
        if (is_null(self::$logger)) {
            self::$logger = LoggerFactory::create('ElementPropertyConditionTable');
        }

        return self::$logger;
    }

}
