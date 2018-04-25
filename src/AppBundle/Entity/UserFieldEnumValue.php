<?php

namespace FourPaws\AppBundle\Entity;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Collection\UserFieldEnumCollection;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserFieldEnumValue
 *
 * @package FourPaws\AppBundle\Entity
 */
class UserFieldEnumValue extends BaseEntity
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("USER_FIELD_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     */
    protected $userFieldId;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("VALUE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $value;
    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("DEF")
     * @Serializer\Groups(groups={"read","update"})
     */
    protected $def = false;
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("SORT")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $sort;
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("XML_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $xmlId;

    /** @var UserFieldEnumService */
    private $userFieldEnumService;
    /** @var UserFieldEnumCollection */
    private $collection;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            if (isset($data['ID'])) {
                $this->setId((int)$data['ID']);
            }
            if (isset($data['USER_FIELD_ID'])) {
                $this->setUserField((int)$data['USER_FIELD_ID']);
            }
            if (isset($data['VALUE'])) {
                $this->setValue($data['VALUE']);
            }
            if (isset($data['DEF'])) {
                $this->setDef($data['DEF']);
            }
            if (isset($data['SORT'])) {
                $this->setSort((int)$data['SORT']);
            }
            if (isset($data['XML_ID'])) {
                $this->setXmlId($data['XML_ID']);
            }
        }
    }

    /**
     * @return int
     */
    public function getUserField() : int
    {
        return $this->userFieldId ?? 0;
    }
    
    /**
     * @param int $userFieldId
     */
    public function setUserField(int $userFieldId)
    {
        $this->userFieldId = $userFieldId;
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->value ?? '';
    }
    
    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isDef() : bool
    {
        return $this->def;
    }

    /**
     * @param int|string|bool $def
     */
    public function setDef($def)
    {
        if (is_bool($def)) {
            $this->def = $def;
        } elseif (is_int($def)) {
            $this->def = (int)$def === 1;
        } else {
            $this->def = $def === static::BITRIX_TRUE;
        }
    }

    /**
     * @return int
     */
    public function getSort() : int
    {
        return $this->sort ?? 0;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getXmlId() : string
    {
        return $this->xmlId ?? '';
    }

    /**
     * @param string $xmlId
     */
    public function setXmlId(string $xmlId)
    {
        $this->xmlId = $xmlId;
    }

    public function getCollection()
    {
        if (!isset($this->collection)) {
            $this->collection = $this->getUserFieldEnumService()->getEnumValueCollection(
                $this->getUserField()
            );
        }

        return $this->collection;

    }

    /**
     * @return UserFieldEnumService
     * @throws ApplicationCreateException
     */
    protected function getUserFieldEnumService() : UserFieldEnumService
    {
        if (!$this->userFieldEnumService) {
            $appCont = Application::getInstance()->getContainer();
            $this->userFieldEnumService = $appCont->get('userfield_enum.service');
        }

        return $this->userFieldEnumService;
    }
}
