<?php

namespace Sprint\Migration;


use Bitrix\Main\Type\DateTime;

class CongratulationBirthDayAgent20181029222251 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Агент для поздравлений с днем рождения";

    public function up(){
        $h = date("H");
        $date = new \DateTime();
        if($h > 10) {
            $date->add(new \DateInterval("P1D"));
        }
        $date->setTime(10, 0, 0);
        $date = new DateTime($date->format("d.m.Y H:i:s"), "d.m.Y H:i:s");
        \CAgent::AddAgent("\FourPaws\PersonalBundle\Controller\CongratulatePetsBirthDayAgent::sendCongratulations();", '', 'Y', 24 * 60 * 60, $date, 'Y', $date);

    }

    public function down(){
        $helper = new HelperManager();
        $helper->Agent()->deleteAgentIfExists('', '\FourPaws\PersonalBundle\Controller\CongratulatePetsBirthDayAgent::sendCongratulations();');
    }

}
