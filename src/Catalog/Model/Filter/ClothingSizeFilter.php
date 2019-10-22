<?php

namespace FourPaws\Catalog\Model\Filter;

use Bitrix\Main\ObjectPropertyException;
use FourPaws\App\Application;
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
            $petSizes = [];

            // todo получать питомцев текущей категории

            /** @var  $userPet */
            foreach ($petService->getCurUserPets() as $userPet) {
                // todo получить размер питомца
                $petSize = '00000003';
                if (!in_array($petSize, $petSizes, true)) {
                    $petSizes[] = $petSize;
                }
            }

            /** @var Variant $variant */
            foreach ($variantCollection as $variant) {
                if (in_array($variant->getValue(), $petSizes, true)) {
                    $variant->withChecked(true);
                }
            }
        } catch (NotAuthorizedException $e) {
        }

        return $variantCollection;
    }
}
