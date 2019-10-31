<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Quest\Pet;
use FourPaws\MobileApiBundle\Dto\Object\Quest\Prize;
use JMS\Serializer\Annotation as Serializer;

class QuestRegisterGetResponse
{
    /**
     * @Serializer\SerializedName("need_register")
     * @Serializer\Type("bool")
     * @var boolean
     */
    protected $needRegister = false;

    /**
     * @Serializer\SerializedName("has_email")
     * @Serializer\Type("bool")
     * @var boolean
     */
    protected $hasEmail = false;

    /**
     * @Serializer\SerializedName("user_email")
     * @Serializer\Type("string")
     * @var string
     */
    protected $userEmail = '';

    /**
     * @Serializer\SerializedName("need_choose_pet")
     * @Serializer\Type("bool")
     * @var boolean
     */
    protected $needChoosePet = false;

    /**
     * @Serializer\SerializedName("pet_types")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Quest\Pet>")
     * @var Pet[]
     */
    protected $petTypes = [];

    /**
     * @Serializer\SerializedName("is_finish_step")
     * @Serializer\Type("bool")
     * @var boolean
     */
    protected $isFinishStep = false;

    /**
     * @Serializer\SerializedName("prizes")
     * @Serializer\Type("<FourPaws\MobileApiBundle\Dto\Object\Quest\Prize>")
     * @var Prize[]
     */
    protected $prizes = [];

    /**
     * @Serializer\SerializedName("show_prize")
     * @Serializer\Type("bool")
     * @var boolean
     */
    protected $showPrize = false;

    /**
     * @Serializer\SerializedName("promocode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $promocode = '';

    /**
     * @Serializer\SerializedName("used_promocode")
     * @Serializer\Type("bool")
     * @var boolean
     */
    protected $usedPromocode = false;

    /**
     * @return bool
     */
    public function isNeedRegister(): bool
    {
        return $this->needRegister;
    }

    /**
     * @param bool $needRegister
     * @return QuestRegisterGetResponse
     */
    public function setNeedRegister(bool $needRegister): QuestRegisterGetResponse
    {
        $this->needRegister = $needRegister;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHasEmail(): bool
    {
        return $this->hasEmail;
    }

    /**
     * @param bool $hasEmail
     * @return QuestRegisterGetResponse
     */
    public function setHasEmail(bool $hasEmail): QuestRegisterGetResponse
    {
        $this->hasEmail = $hasEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * @param string $userEmail
     * @return QuestRegisterGetResponse
     */
    public function setUserEmail(string $userEmail): QuestRegisterGetResponse
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedChoosePet(): bool
    {
        return $this->needChoosePet;
    }

    /**
     * @param bool $needChoosePet
     * @return QuestRegisterGetResponse
     */
    public function setNeedChoosePet(bool $needChoosePet): QuestRegisterGetResponse
    {
        $this->needChoosePet = $needChoosePet;
        return $this;
    }

    /**
     * @return Pet[]
     */
    public function getPetTypes(): array
    {
        return $this->petTypes;
    }

    /**
     * @param Pet[] $petTypes
     * @return QuestRegisterGetResponse
     */
    public function setPetTypes(array $petTypes): QuestRegisterGetResponse
    {
        $this->petTypes = $petTypes;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFinishStep(): bool
    {
        return $this->isFinishStep;
    }

    /**
     * @param bool $isFinishStep
     * @return QuestRegisterGetResponse
     */
    public function setIsFinishStep(bool $isFinishStep): QuestRegisterGetResponse
    {
        $this->isFinishStep = $isFinishStep;
        return $this;
    }

    /**
     * @return Prize[]
     */
    public function getPrizes(): array
    {
        return $this->prizes;
    }

    /**
     * @param Prize[] $prizes
     * @return QuestRegisterGetResponse
     */
    public function setPrizes(array $prizes): QuestRegisterGetResponse
    {
        $this->prizes = $prizes;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowPrize(): bool
    {
        return $this->showPrize;
    }

    /**
     * @param bool $showPrize
     * @return QuestRegisterGetResponse
     */
    public function setShowPrize(bool $showPrize): QuestRegisterGetResponse
    {
        $this->showPrize = $showPrize;
        return $this;
    }

    /**
     * @return string
     */
    public function getPromocode(): string
    {
        return $this->promocode;
    }

    /**
     * @param string $promocode
     * @return QuestRegisterGetResponse
     */
    public function setPromocode(string $promocode): QuestRegisterGetResponse
    {
        $this->promocode = $promocode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUsedPromocode(): bool
    {
        return $this->usedPromocode;
    }

    /**
     * @param bool $usedPromocode
     * @return QuestRegisterGetResponse
     */
    public function setUsedPromocode(bool $usedPromocode): QuestRegisterGetResponse
    {
        $this->usedPromocode = $usedPromocode;
        return $this;
    }
}
