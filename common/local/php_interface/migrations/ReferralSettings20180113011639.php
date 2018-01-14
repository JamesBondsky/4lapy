<?php

namespace Sprint\Migration;

use FourPaws\PersonalBundle\Controller\ReferralUpdateAgent;

class ReferralSettings20180113011639 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected $description = 'настройка реферала';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $siteId = 's1';
        try {
            $helper->Event()->addEventTypeIfNotExists(
                'ReferralAdd',
                [
                    'LID'         => $siteId,
                    'NAME'        => 'Добавление реферала',
                    'DESCRIPTION' => '#CARD# - номер карты',
                ]
            );
        } catch (Exceptions\HelperException $e) {
        }
        try {
            $helper->Event()->addEventMessageIfNotExists(
                'ReferralAdd',
                [
                    'LID'      => $siteId,
                    'EMAIL_TO' => '#DEFAULT_EMAIL_FROM#',
                    'SUBJECT'  => 'Новый реферал',
                    'MESSAGE'  => 'Добавлен новый реферал с номером карты #CARD#. Необходимо произвести модерацию',
                ]
            );
        } catch (Exceptions\HelperException $e) {
        }
        
        $helper->Agent()->addAgentIfNotExists(
            'main',
            '\\' . ReferralUpdateAgent::class . '::updateModerateReferrals();',
            1800,
            ''
        );
    }
    
    public function down()
    {
        $helper = new HelperManager();
        $helper->Agent()->deleteAgentIfExists(
            'main',
            '\\' . ReferralUpdateAgent::class . '::updateModerateReferrals();'
        );
    }
    
}
