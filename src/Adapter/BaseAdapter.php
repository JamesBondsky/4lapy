<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.18
 * Time: 9:22
 */

namespace FourPaws\Adapter;


use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use JMS\Serializer\SerializerInterface;

abstract class BaseAdapter implements BaseAdapterInterface
{
    /** @var SerializerInterface  */
    private $arrayTransformer;

    /**
     * BaseAdapter constructor.
     */
    public function __construct()
    {
        try {
            $this->arrayTransformer = Application::getInstance()->getContainer()->get(SerializerInterface::class);
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('system');
            $logger->error('ошибка загрузки сервиса - '.$e->getMessage());
        }
    }

    /**
     * @param array  $data
     * @param string $class
     *
     * @return mixed
     */
    public function convertDataToEntity(array $data, string $class)
    {
        return $this->arrayTransformer->fromArray($data, $class);
    }

    /**
     * @param $entity
     *
     * @return array|mixed
     */
    public function convertEntityToData($entity)
    {
        return $this->arrayTransformer->toArray($entity);
    }

    /**
     * @param mixed $entity
     *
     * @return mixed
     */
    abstract public function convert($entity);
}