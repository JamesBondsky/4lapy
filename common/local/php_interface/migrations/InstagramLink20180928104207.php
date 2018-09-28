<?php

namespace Sprint\Migration;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class InstagramLink20180928104207 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавление переменной сайта со ссылкой на группу в инстаграмме в подвале сайта";

    public function up(){
        \CASDOption::SetOption('social_link_in', 'https://www.instagram.com/4lapy.ru/', 's1');
    }

    public function down(){
        \CASDOption::RemoveOption('social_link_in', 's1');
    }

    /**
     * InstagramLink20180928104207 constructor.
     *
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule('asd.tplvars');

        parent::__construct();
    }
}
