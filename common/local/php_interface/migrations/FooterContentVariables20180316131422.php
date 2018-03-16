<?php

namespace Sprint\Migration;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * Миграция для заполнения новых переменных сайта
 *
 * Class FooterContentVariables20180316131422
 *
 * @package Sprint\Migration
 */
class FooterContentVariables20180316131422 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Заполнение новых переменных сайта';

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * Set options
     */
    public function up()
    {
        \CASDOption::SetOption('application_ios', 'https://itunes.apple.com/us/app/%D1%87%D0%B5%D1%82%D1%8B%D1%80%D0%B5-%D0%BB%D0%B0%D0%BF%D1%8B-%D0%B7%D0%BE%D0%BE%D0%BC%D0%B0%D0%B3%D0%B0%D0%B7%D0%B8%D0%BD/id1222315361?mt=8', 'Ссылка на приложение ios', 's1');
        \CASDOption::SetOption('application_android', 'javascript:void(0);', 'Ссылка на приложение android', 's1');
        \CASDOption::SetOption('rating_yandex', 'https://market.yandex.ru/shop/155471/reviews', 'Ссылка на рейтинг Yandex', 's1');
        \CASDOption::SetOption('shops_subtitle', 'Все магазины нашей сети работают без выходных и принимают банковские карты к оплате', 'Подзаголовок в разделе "Магазины"', 's1');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * Remove options
     */
    public function down()
    {
        \CASDOption::RemoveOption('application_ios', 's1');
        \CASDOption::RemoveOption('application_android', 's1');
        \CASDOption::RemoveOption('rating_yandex', 's1');
        \CASDOption::RemoveOption('shops_subtitle', 's1');
    }

    /**
     * FooterContentVariables20180316131422 constructor.
     *
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule('asd.tplvars');

        parent::__construct();
    }
}
