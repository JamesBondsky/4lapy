<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Response;

interface ProviderInterface
{
    /**
     * $map - однозначное отображение ['поле на сервере' => 'поле на клиенте']
     * Так же возможно однозначное указание сущности для позднего связывания.
     *
     * Работает следующим образом:
     *
     * Отображение задаётся в виде ['имя сущности'.'поле на сервере' => 'поле на клиенте']
     *
     * При разборе ответа вместо записи в это поле осуществляется запись в таблицу adv_migrator_lazy
     * При любом импорте провайдер после завершения импорта разбирает относящиеся к своей сущности id'шники и, если
     * у него есть, что отдать, записывает значение, удаляя его из таблицы.
     *
     * @return array
     */
    public function getMap() : array;
    
    /**
     * @return \FourPaws\Migrator\Converter\ConverterInterface[] array
     */
    public function getConverters() : array;

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response);
    
    /**
     * @param EntityInterface $entity
     *
     * @return void
     */
    public function setEntity(EntityInterface $entity);
}