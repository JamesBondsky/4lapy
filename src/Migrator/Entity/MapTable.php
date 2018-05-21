<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\SystemException;

/**
 * ClassMapTable
 *
 * @package FourPaws\Migrator\Entity
 */
class MapTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName() : string
    {
        return 'adv_migrator_map';
    }

    /**
     * @return array
     *
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getMap() : array
    {
        return [
            'ID'            => new IntegerField('ID', [
                'primary'      => true,
                'autocomplete' => true,
                'title'        => 'Идентификатор',
            ]),
            'ENTITY'        => new StringField('ENTITY', [
                'title'    => 'Сущность',
                'required' => true,
            ]),
            'INTERNAL_ID'   => new StringField('INTERNAL_ID', [
                'title' => 'Внутренний идентификатор',
            ]),
            'EXTERNAL_ID'   => new StringField('EXTERNAL_ID', [
                'title'    => 'Внешний идентификатор',
                'required' => true,
            ]),
            'ENTITY_ENTITY' => new ReferenceField('ENTITY_ENTITY',
                                                  '\FourPaws\Migrator\Entity\Entity',
                                                  ['=this.ENTITY' => 'ref.ENTITY'],
                                                  ['join_type' => 'left']),
        ];
    }

    /**
     * @param string $external
     * @param string $entity
     *
     * @return bool
     *
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function isInternalEntityExists(string $external, string $entity) : bool
    {
        return self::getList([
                                 'filter' => [
                                     'EXTERNAL_ID'  => $external,
                                     '!INTERNAL_ID' => false,
                                     'ENTITY'       => $entity,
                                 ],
                                 'select' => ['ID'],
                             ])->getSelectedRowsCount() === 1;
    }

    /**
     * @param string $external
     * @param string $entity
     *
     * @return string
     *
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getInternalIdByExternalId(string $external, string $entity) : string
    {
        /**
         * @var array $result
         */
        $result = self::getList([
            'filter' => [
                'EXTERNAL_ID' => $external,
                'ENTITY'      => $entity,
            ],
            'select' => ['INTERNAL_ID'],
        ])->fetch();

        return $result ? (string)$result['INTERNAL_ID'] : '';
    }

    /**
     * @param string $internal
     * @param string $entity
     *
     * @return string
     *
     * @throws SystemException
     * @throws ArgumentException
     */
    public static function getExternalIdByInternalId(string $internal, string $entity) : string
    {
        /**
         * @var array $result
         */
        $result = self::getList([
            'filter' => [
                'INTERNAL_ID' => $internal,
                'ENTITY'      => $entity,
            ],
            'select' => ['EXTERNAL_ID'],
        ])->fetch();

        return $result ? (string)$result['EXTERNAL_ID'] : '';
    }
    
    /**
     * @param array  $external
     * @param string $entity
     *
     * @return array
     *
     * @throws ArgumentException
     */
    public static function getInternalIdListByExternalIdList(array $external, string $entity) : array
    {
        $result = self::getList([
                                    'filter' => [
                                        '@EXTERNAL_ID' => $external,
                                        'ENTITY'       => $entity,
                                    ],
                                    'select' => ['INTERNAL_ID'],
                                ])->fetchAll();
        
        return \array_column($result, 'INTERNAL_ID');
    }
    
    /**
     * @param array  $internal
     * @param string $entity
     *
     * @return array
     *
     * @throws ArgumentException
     */
    public static function getExternalListIdByInternalIdList(array $internal, string $entity) : array
    {
        $result = self::getList([
                                    'filter' => [
                                        '@INTERNAL_ID' => $internal,
                                        'ENTITY'       => $entity,
                                    ],
                                    'select' => ['EXTERNAL_ID'],
                                ])->fetchAll();
        
        return \rray_column($result, 'EXTERNAL_ID');
    }
    
    /**
     * @param string $entity
     * @param string $externalId
     * @param string $internalId
     *
     * @return \Bitrix\Main\Entity\AddResult
     *
     * @throws \Exception
     */
    public static function addEntity(string $entity, string $externalId, string $internalId) : AddResult
    {
        return parent::add([
                               'ENTITY'      => $entity,
                               'EXTERNAL_ID' => $externalId,
                               'INTERNAL_ID' => $internalId,
                           ]);
    }
    
    /**
     * Возвращает полное отображение для сущности entity вида
     *
     * [
     *   EXTERNAL_ID_1 => INTERNAL_ID_1,
     *   EXTERNAL_ID_2 => INTERNAL_ID_2,
     *   ...,
     * ]
     *
     * @param string $entity
     *
     * @return array
     *
     * @throws ArgumentException
     */
    public static function getFullMapByEntity(string $entity) : array
    {
        $map = self::getList([
                                 'filter' => ['=ENTITY' => $entity],
                                 'select' => [
                                     'EXTERNAL_ID',
                                     'INTERNAL_ID',
                                 ],
                             ])->fetchAll();
        
        return \array_combine(\array_column($map, 'EXTERNAL_ID'), \array_column($map, 'INTERNAL_ID'));
    }
}
