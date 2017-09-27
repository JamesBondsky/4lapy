<?php

namespace FourPaws\BitrixIblockORM\Model;

use Adv\Bitrixtools\Tools\BitrixUtils;

abstract class HLBItemBase extends BitrixArrayItemBase
{
    /**
     * @var bool
     * На перспективу, чтобы создав UF_ACTIVE типа "Да/Нет" можно было получить сразу готовый флаг активности.
     */
    protected $active = true;

    public function __construct(array $fields = [])
    {
        parent::__construct($fields);

        foreach ($fields as $field => $value) {

            //Если не начинается с 'UF_'
            if ('UF_' !== substr($field, 0, 3)) {
                continue;
            }

            $fieldName = substr($field, 3);

            /**
             * Инициализация обычных полей
             */
            if (property_exists($this, $fieldName)) {
                $this->$fieldName = $value;
            } /**
             * Установка активности.
             */
            elseif ('ACTIVE' === $fieldName) {
                $this->withActive(BitrixUtils::bitrixBool2bool($value));
            }

        }
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function withActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }
}
