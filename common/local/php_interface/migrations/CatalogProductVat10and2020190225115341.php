<?php

namespace Sprint\Migration;


class CatalogProductVat10and2020190225115341 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Изменение НДС с 18 на 20% и отдельная запись для ветаптеки с НДС 10%";

    protected $vatData10 = [
        'NAME' => 'НДС 10%',
        'RATE' => '10.00',
        'C_SORT' => '300',
        'ACTIVE' => 'Y'
    ];

    public function up()
    {

        //обновляем 18 на 20
        $vatId18 = null;
        $vatData18 = [];
        $vatList = \CCatalogVat::GetListEx();
        while ($vat = $vatList->Fetch()) {
            if ((int)$vat['RATE'] === 18) {
                $vatId18 = $vat['ID'];
                $vatData18 = $vat;
                unset($vatData18['ID'], $vatData18['TIMESTAMP_X']);
                $vatData18['NAME'] = 'НДС 20%';
                $vatData18['RATE'] = '20.00';
            }
        }

        if ($vatId18 != null && !empty($vatData18)) {
            \CCatalogVat::Update($vatId18, $vatData18);
        }

        \CCatalogVat::Add($this->vatData10);

        return true;
    }

    public function down()
    {
        //Empty
    }

}
