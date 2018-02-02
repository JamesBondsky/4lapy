<?php

namespace FourPaws\PersonalBundle\Repository;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class AddressRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class AddressRepository extends BaseHlRepository
{
    const HL_NAME = 'Address';

    /** @var Address $entity */
    protected $entity;

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws BitrixRuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function create(): bool
    {
        if ($this->entity->getUserId() === 0) {
            $this->entity->setUserId(
                App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUserId()
            );
        }

        return parent::create();
    }

    /**
     * @param array $params
     *
     * @return Address[]|array
     * @throws \Exception
     */
    public function findBy(array $params = []): array
    {
        if (empty($params['entityClass'])) {
            $params['entityClass'] = Address::class;
        }

        return parent::findBy($params);
    }

    /**
     * @param int $id
     *
     * @return Address
     * @throws \Exception
     */
    public function findById(int $id): Address
    {
        $result = parent::findBy(['filter' => ['ID' => $id]]);
        if (!$address = reset($result)) {
            throw new NotFoundException('Address not found');
        }

        return $address;
    }

    /**
     * @param int $userId
     * @param string $locationCode
     *
     * @return array
     */
    public function findByUser(int $userId = 0, string $locationCode = ''): array
    {
        if (!$userId) {
            $userId = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class)->getCurrentUserId();
        }

        $filter = [
            'UF_USER_ID' => $userId,
        ];

        if ($locationCode) {
            $filter['UF_CITY_LOCATION'] = $locationCode;
        }

        return $this->findBy(['filter' => $filter]);
    }
}
