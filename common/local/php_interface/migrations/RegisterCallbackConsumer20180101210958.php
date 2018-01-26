<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

class RegisterCallbackConsumer20180101210958 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    const CONSUMER_NAME = 'callback_set';
    
    protected $description = 'Регистрация консьюмера обратного звонка';
    
    public function up()
    {
        exec(
            'cd ' . \dirname($_SERVER['DOCUMENT_ROOT']) . ''
            . '; bin/symfony_console rabbitmq:consumer callback_set -vvv > /dev/null 2>&1 &'
        );
    }
    
    public function down()
    {
    }
}
