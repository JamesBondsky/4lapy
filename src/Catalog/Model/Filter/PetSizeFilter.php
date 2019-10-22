<?php

namespace FourPaws\Catalog\Model\Filter;

use Exception;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;
use FourPaws\Catalog\Model\Variant;
use FourPaws\PersonalBundle\Service\PetService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use Symfony\Component\HttpFoundation\Request;

class PetSizeFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.petsize';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'PetSize';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'PET_SIZE';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_PET_SIZE';
    }

    /**
     * @return VariantCollection
     * @throws Exception
     * @throws Exception
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

        $clearPetSizeFilter = $request->cookies->get('clear_pet_size_filter');
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
