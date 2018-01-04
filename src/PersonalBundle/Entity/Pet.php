<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Entity;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\Type\Date;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\Helpers\WordHelper;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class Pet extends BaseEntity
{
    const PET_TYPE = 'PetType';
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_NAME")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $name = '';
    
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $userId;
    
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_FILE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $photo;
    
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_TYPE")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $type;
    
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_BREED")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $breed;
    
    /**
     * @var Date
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_BIRTHDAY")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $birthday;
    
    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_GENDER")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     * @Assert\NotBlank(groups={"create","read","update","delete"})
     */
    protected $gender;
    
    protected $stringType   = '';
    
    protected $stringGender = '';
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }
    
    /**
     * @param int $userId
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }
    
    /**
     * @return string
     */
    public function getImgPath() : string
    {
        return \CFile::GetPath($this->getPhoto());
    }
    
    /**
     * @return int
     */
    public function getPhoto() : int
    {
        return $this->photo;
    }
    
    /**
     * @param int $photo
     */
    public function setPhoto(int $photo)
    {
        $this->photo = $photo;
    }
    
    /**
     * @return string
     */
    public function getResizeImgPath()
    {
        try {
            return CropImageDecorator::createFromPrimary($this->getPhoto())
                                     ->setCropWidth(110)
                                     ->setCropHeight(110)
                                     ->getSrc();
        } catch (FileNotFoundException $e) {
        }
    }
    
    /**
     * @return string
     */
    public function getStringType() : string
    {
        if (empty($this->stringType) && $this->getType() > 0) {
            try {
                $this->setStringType($this->getType());
            } catch (\Exception $e) {
            }
        }
        
        return $this->stringType;
    }
    
    /**
     * @param int $type
     *
     * @throws \Exception
     */
    protected function setStringType(int $type)
    {
        $this->stringType =
            HLBlockFactory::createTableObject(static::PET_TYPE)::query()->setFilter(['ID' => $type])->setSelect(
                ['UF_NAME']
            )->exec()->fetch()['UF_NAME'];
    }
    
    /**
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }
    
    /**
     * @param int $type
     *
     * @throws \Exception
     */
    public function setType(int $type)
    {
        $this->type = $type;
        if ($type > 0) {
            $this->setStringType($type);
        }
    }
    
    /**
     * @return string
     */
    public function getBreed() : string
    {
        return $this->breed;
    }
    
    /**
     * @param string $breed
     */
    public function setBreed(string $breed)
    {
        $this->breed = $breed;
    }
    
    public function getYearsString()
    {
        $years = $this->getYears();
        
        return $years . ' ' . WordHelper::declension(
                $years,
                [
                    'год',
                    'года',
                    'лет',
                ]
            );
    }
    
    public function getYears()
    {
        $date     = new \DateTime($this->getBirthday()->format('Y-m-d'));
        $interval = $date->diff(new \DateTime(date('Y-m-d')));
        
        return (int)$interval->format('%Y') + ((float)$interval->format('%m') / 12);
    }
    
    /**
     * @return Date
     */
    public function getBirthday() : Date
    {
        return $this->birthday;
    }
    
    /**
     * @param string $birthday
     */
    public function setBirthday(string $birthday)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->birthday = new Date($birthday, 'd.m.Y');
    }
    
    /**
     * @return string
     */
    public function getStringGender() : string
    {
        if (empty($this->stringGender) && $this->getGender() > 0) {
            $this->setStringGender($this->getGender());
        }
        
        return $this->stringGender;
    }
    
    /**
     * @param int $gender
     */
    protected function setStringGender(int $gender)
    {
        $userFieldEnum      = new \CUserFieldEnum();
        $this->stringGender = $userFieldEnum->GetList([], ['ID' => $gender])->Fetch()['VALUE'];
    }
    
    /**
     * @return int
     */
    public function getGender() : int
    {
        return $this->gender;
    }
    
    /**
     * @param int $gender
     */
    public function setGender(int $gender)
    {
        $this->gender = $gender;
        if ($gender > 0) {
            $this->setStringGender($gender);
        }
    }
}
