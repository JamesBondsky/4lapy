<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class FrontOfficeSiteTemplate20180201161500 extends SprintMigrationBase
{
    const SITE_TEMPLATE_NAME = '4paws_front_office';
    const SITE_CODE = 's1';

    protected $description = 'Добавление шаблона сайта для ЛК магазина';

    public function up()
    {
        $result = false;
        $siteHelper = $this->getHelper()->Site();
        $siteTemplates = $siteHelper->getSiteTemplates(static::SITE_CODE);
        foreach ($siteTemplates as $template) {
            if ($template['TEMPLATE'] == static::SITE_TEMPLATE_NAME) {
                $result = true;
                $this->log()->info('Шаблон уже привязан к сайту');
                break;
            }
        }
        if (!$result) {
            $siteTemplates[] = [
                'TEMPLATE' => static::SITE_TEMPLATE_NAME,
                'IN_DIR' => '/front-office'
            ];
            $success = $siteHelper->setSiteTemplates(static::SITE_CODE, $siteTemplates);
            if ($success) {
                $result = true;
                $this->log()->info('Шаблон успешно привязан к сайту');
            } else {
                $this->log()->error('Ошибка привязки шаблона к сайту');
            }
        }

        return $result;
    }

    public function down()
    {
        return true;
    }
}
