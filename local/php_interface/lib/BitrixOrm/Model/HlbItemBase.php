<?php

namespace FourPaws\BitrixOrm\Model;

abstract class HlbItemBase extends BitrixArrayItemBase
{

    public function __construct(array $fields = [])
    {
        //TODO ДОбавить обнаружение полей UF_*
        //TODO Разнести работу через D7 и с HL-блоками, чтобы не мешать в один класс. 1


        parent::__construct($fields);

        /**
         * На перспективу, чтобы создав UF_ACTIVE типа "Да/Нет" можно было получить сразу готовый флаг активности.
         */
        if (isset($fields['UF_ACTIVE'])) {
            $this->withActive((bool)$fields['UF_ACTIVE']);
        }

    }

}
