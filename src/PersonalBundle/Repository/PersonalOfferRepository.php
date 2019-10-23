<?php


namespace FourPaws\PersonalBundle\Repository;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use CIBlockElement;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\PersonalBundle\Exception\AlreadyExistsException;
use FourPaws\PersonalBundle\Exception\BaseException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\OfferNotFoundException;

class PersonalOfferRepository
{

    /**
     * Добавляет персональное предложение в инфоблок
     *
     * @param string $name
     * @param string|null $description
     * @throws BaseException
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws AlreadyExistsException
     */
    public function add(string $name, ?string $description = ''): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }
        if (!$name) {
            throw new InvalidArgumentException(InvalidArgumentException::ERRORS[3], 3);
        }

        $isOfferExists = $this->exists($name);
        if ($isOfferExists) {
            throw new AlreadyExistsException(AlreadyExistsException::ERRORS[1], 1);
        }

        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS);

        $el = new CIBlockElement;
        $fields = [
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID'         => $iblockId,
            'NAME'              => $name,
            'ACTIVE'            => 'Y',
            'PREVIEW_TEXT'      => $description,
        ];
        if (!$elementId = $el->Add($fields)) {
            throw new BaseException($el->LAST_ERROR);
        }
    }

    /**
     * @param string $name
     * @return bool
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function exists(string $name): bool
    {
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS);

        $isOfferExists = (bool)CIBlockElement::GetList([], [
            '=IBLOCK_ID' => $iblockId,
            '=NAME'      => $name,
        ], []);

        return $isOfferExists;
    }

    /**
     * @param array $filter
     * @return int
     * @throws OfferNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function getId(array $filter): int
    {
        return $this->get($filter)['ID'];
    }

    /**
     * @param array $filter
     * @param array|null $select
     * @return array
     * @throws OfferNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    private function get(array $filter, ?array $select = []): array
    {
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS);

        $arFilter = [
            '=IBLOCK_ID' => $iblockId,
        ];
        $arFilter = array_unique(array_merge($arFilter, $filter));

        $arSelect = ['ID', 'IBLOCK_ID'];
        $arSelect = array_unique(array_merge($arSelect, $select));

        $rsPersonalOffer = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

        $personalOffer = $rsPersonalOffer->Fetch();

        if (!$personalOffer) {
            throw new OfferNotFoundException('Персональное предложение не найдено');
        }

        return $personalOffer;
    }

    /**
     * @param int $id
     * @param string|null $description
     * @throws BaseException
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function update(int $id, ?string $description = ''): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        $el = new CIBlockElement;
        $arFields = [
            'PREVIEW_TEXT'    => $description,
        ];

        if (!$res = $el->Update($id, $arFields)) {
            throw new BaseException($res->LAST_ERROR);
        }
    }
}