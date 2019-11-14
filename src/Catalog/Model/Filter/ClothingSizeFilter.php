<?php

namespace FourPaws\Catalog\Model\Filter;

use Bitrix\Main\ObjectPropertyException;
use FourPaws\App\Application;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;
use FourPaws\Catalog\Model\Variant;
use FourPaws\PersonalBundle\Service\PetService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ClothingSizeFilter
 *
 * @package FourPaws\Catalog\Model\Filter
 */
class ClothingSizeFilter extends ReferenceFilterNested
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.clothingsize';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'ClothingSize';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'CLOTHING_SIZE';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'offers';
    }

    /**
     * @return string
     */
    public function getNestedRuleCode(): string
    {
        return 'PROPERTY_CLOTHING_SIZE';
    }

    /**
     * @return VariantCollection
     * @throws Exception
     * @throws Exception
     * @throws ObjectPropertyException
     */
    protected function doGetAllVariants(): VariantCollection
    {
        $variantCollection = parent::doGetAllVariants();

        // костыль, чтобы фильтр выставлялся только на странице с одеждой для собак
        if (strpos($_SERVER['REQUEST_URI'], '/catalog/sobaki/odezhda-i-obuv/') !== 0) {
            return $variantCollection;
        }

        $request = Request::createFromGlobals();

        $checkedValues = $this->getCheckedValues($request);

        /* Если в запросе есть фильтр для данной категории, то ничего не выставляем */
        if (!empty($checkedValues)) {
            return $variantCollection;
        }

        $clearPetSizeFilter = $request->cookies->get('clear_clothing_size_filter');
        if ($clearPetSizeFilter && ((int)$clearPetSizeFilter === 1)) {
            return $variantCollection;
        }

        /** @var PetService $petService */
        $petService = Application::getInstance()->getContainer()->get(PetService::class);
        try {
            /* Для фильтра значения хначения хранятся в отдельном инфоблоке, а у питомцев размер одежды выбирается из списка и эти два списка не связаны, поэтому сравниваем по названию */
            /* на момент реализации функционала данный фильтр есть только у собак, а пользователь указывает в личном кабинете размер одежды тоже только для собак, поэтому нет проверки на сравнение типа питомца пользователя и типа питомца на текущей странице */
            $petSizes = [];

            /** @var UserFieldEnumValue $petSize */
            foreach ($petService->getCurUserPetSizes() as $petSize) {
                if (!in_array($petSize->getValue(), $petSizes, true)) {
                    $petSizes[] = $petSize->getValue();
                }
            }

            if (empty($petSizes)) {
                return $variantCollection;
            }

            /** @var Variant $variant */
            foreach ($variantCollection as $variant) {
                if (in_array($variant->getName(), $petSizes, true)) {
                    $variant->withChecked(true);
                }
            }
        } catch (NotAuthorizedException $e) {
        }

        return $variantCollection;
    }
}
