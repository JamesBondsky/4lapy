<?php

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrmBundle\Orm\D7Repository;
use FourPaws\SaleBundle\Entity\ForgotBasket;
use FourPaws\SaleBundle\Enum\ForgotBasketEnum;
use FourPaws\SaleBundle\Exception\ForgotBasket\NotFoundException;
use FourPaws\SaleBundle\Exception\ForgotBasket\UnknownTypeException;

class ForgotBasketRepository extends D7Repository
{
    /**
     * @param int    $userId
     * @param string $type
     *
     * @return ForgotBasket
     * @throws NotFoundException
     * @throws UnknownTypeException
     * @throws SystemException
     */
    public function findByUserId(int $userId, string $type = ForgotBasketEnum::TYPE_NOTIFICATION): ForgotBasket
    {
        $filter = \array_merge(['UF_USER_ID' => $userId], $this->getTypeFilter($type));
        $result = parent::findBy($filter)->first();
        if (!$result instanceof ForgotBasket) {
            throw new NotFoundException(\sprintf('Task for user #%s not found', $userId));
        }

        return $result;
    }

    /**
     * @param string $type
     * @param bool   $useDateFilter
     *
     * @return Collection
     * @throws SystemException
     * @throws UnknownTypeException
     */
    public function getActive(string $type, bool $useDateFilter): Collection
    {
        $filter = [
            'UF_ACTIVE' => true,
        ];

        $filter = \array_merge($filter, $this->getTypeFilter($type));

        if ($useDateFilter) {
            $filter = \array_merge($filter, $this->getDateFilter($type));
        }

        return $this->findBy($filter);
    }

    /**
     * @param int $typeId
     *
     * @return string
     * @throws SystemException
     * @throws UnknownTypeException
     */
    public function getTypeCodeById(int $typeId): string
    {
        $types = $this->getTypes();

        if (null === $types[$typeId]) {
            throw new UnknownTypeException(\sprintf('Type with id %s not found', $typeId));
        }

        return $types[$typeId];
    }

    /**
     * @param string $code
     *
     * @return int
     * @throws SystemException
     * @throws UnknownTypeException
     */
    public function getTypeIdByCode(string $code): ?int
    {
        $types = array_flip($this->getTypes());

        if (null === $types[$code]) {
            throw new UnknownTypeException(\sprintf('Type with code %s not found', $code));
        }

        return $types[$code];
    }

    /**
     * @return array
     * @throws SystemException
     */
    public function getTypes(): array
    {
        $enums = (new \CUserFieldEnum())->GetList([], ['USER_FIELD_NAME' => ForgotBasketEnum::TYPE_FIELD_CODE]);

        $result = [];
        while ($enum = $enums->Fetch()) {
            $result[(int)$enum['ID']] = $enum['XML_ID'];
        }

        return $result;
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws UnknownTypeException
     * @throws SystemException
     */
    protected function getTypeFilter(string $type): array
    {
        $types = \array_flip($this->getTypes());

        switch (true) {
            case ForgotBasketEnum::TYPE_NOTIFICATION:
            case ForgotBasketEnum::TYPE_REMINDER:
                $result = [ForgotBasketEnum::TYPE_FIELD_CODE => $types[$type]];
                break;
            default:
                throw new UnknownTypeException(\sprintf('Unknown type %s', $type));
        }

        return $result;
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws UnknownTypeException
     */
    protected function getDateFilter(string $type): array
    {
        $date = new \DateTime();
        $filter = [];
        switch ($type) {
            case ForgotBasketEnum::TYPE_NOTIFICATION:
                $date->setTimestamp(time() - ForgotBasketEnum::INTERVAL_NOTIFICATION);
                $filter['<UF_DATE_EXEC'] = (new \DateTime())->setTimestamp(time() - ForgotBasketEnum::BLOCK_NOTIFICATION);
                break;
            case ForgotBasketEnum::TYPE_REMINDER:
                $date->setTimestamp(time() - ForgotBasketEnum::INTERVAL_REMINDER);
                break;
            default:
                throw new UnknownTypeException(\sprintf('Unknown type %s', $type));
        }

        $filter['<UF_DATE_UPDATE'] = $date;

        return $filter;
    }
}
