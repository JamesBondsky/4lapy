<?php

namespace Sprint\Migration;


use FourPaws\Helpers\Table\FormTable;

class RenameFormTitle20180503103341 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = 'Изменение текстов форм';

    public function up(){
        $formId = (int)FormTable::query()->setSelect(['ID'])->setFilter(['SID'=>'callback'])->exec()->fetch()['ID'];
        if($formId > 0){
            FormTable::update($formId,['DESCRIPTION'=>'<dl class="b-phone-pair">
    <dt class="b-phone-pair__phone b-phone-pair__phone--small-blue">Есть вопросы?</dt>
    <dd class="b-phone-pair__description">Оставьте телефон, мы Вам перезвоним</dd>
</dl>']);
        }
    }
}
