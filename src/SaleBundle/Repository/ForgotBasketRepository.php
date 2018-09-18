<?php

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Main\SystemException;
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
     */
    public function findByUserId(int $userId, string $type = ForgotBasketEnum::TYPE_ALL): ForgotBasket
    {
        $filter = \array_merge(['UF_USER_ID' => $userId], $this->getTypeFilter($type));
        $result = parent::findBy($filter)->first();
        if (!$result instanceof ForgotBasket) {
            throw new NotFoundException(\sprintf('Task for user #%s not found', $userId));
        }

        return $result;
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
     */
    protected function getTypeFilter(string $type): array
    {
        switch (true) {
            case ForgotBasketEnum::TYPE_NOTIFICATION:
            case ForgotBasketEnum::TYPE_REMINDER:
                $result = ['UF_TASK_TYPE' => $type];
                break;
            case ForgotBasketEnum::TYPE_ALL:
                $result = [
                    'LOGIC' => 'OR',
                    ['UF_TASK_TYPE' => ForgotBasketEnum::TYPE_REMINDER],
                    ['UF_TASK_TYPE' => ForgotBasketEnum::TYPE_NOTIFICATION],
                ];
                break;
            default:
                throw new UnknownTypeException(\sprintf('Unknown type %s', $type));
        }

        return $result;
    }
}
