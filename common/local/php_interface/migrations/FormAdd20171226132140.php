<?php

namespace Sprint\Migration;


class FormAdd20171226132140 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Настройка форм";

    public function up(){
        /** @todo create form */
    
        \Bitrix\Main\Config\Option::set('form', 'SIMPLE', 'N');

    }

    public function down(){
    
    }

}
