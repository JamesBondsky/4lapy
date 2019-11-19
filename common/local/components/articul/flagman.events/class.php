<?php

use GuzzleHttp\Client;

/**
 * Class FlagmanTraining
 */
class FlagmanEvents extends \CBitrixComponent
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;
    
    /**
     * @var string
     */
    private $token = 'dsvbgdfFBn5434tyhFfd544gdfbDS4ggdsDSDtf';
    
    /**
     * @var string
     */
    private $path = 'get-schedule/';
    
    /**
     * @var array|false|string
     */
    private $url;
    
    /**
     * @var $actionTime
     */
    private $actionTime;
    
    public function onPrepareComponentParams($arParams)
    {
        $this->url          = getenv('VET_CLINIC') . $this->path . $arParams['EVENT_NAME'] . '/';
        $this->guzzleClient = new Client();
        
        return parent::onPrepareComponentParams($arParams);
    }
    
    /**
     * @return mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function executeComponent()
    {
        $response = $this->guzzleClient->request('GET', $this->url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        
        $body = $response->getBody();
        
        $this->arResult['SCHEDULE'] = json_decode($body->getContents(), true);

        $this->modifyTime();
        $this->checkEmptiness();
        
        $this->sortDays();

        $this->includeComponentTemplate();
    }
    
    private function checkEmptiness()
    {
        foreach ($this->arResult['SCHEDULE'] as $key => $day) {
            if (empty($day['times'])) {
                $this->arResult['SCHEDULE'][$key]['end'] = 'Y';
                continue;
            }
            
            foreach ($day['times'] as $timeKey => $time) {
                $endTimestamp = strtotime($timeKey) + strtotime($this->actionTime) -strtotime("00:00:00");
                $endTime = date('H:i', $endTimestamp);

                $this->arResult['SCHEDULE'][$key]['times'][$timeKey]['interval'] = $timeKey . ' - ' . $endTime;
            }
            $this->arResult['SCHEDULE'][$key]['end'] = 'N';
        }
    }
    
    private function modifyTime()
    {
        $actionTime = current($this->arResult['SCHEDULE'])['exec'];
        
        $hours = $actionTime / 60;
        $minutes = $actionTime % 60;
        
        $this->actionTime =  (int) $hours .':'. $minutes;
    }
    
    private function sortDays()
    {
        uasort($this->arResult['SCHEDULE'], function ($a, $b) {
            preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $a['day'], $matchesA);
            preg_match('/([0-9]{2,4}).([0-9]{2,4}).([0-9]{2,4})/', $b['day'], $matchesB);
        
            return (strtotime($matchesA[0]) > strtotime($matchesB[0])) ? 1 : -1;
        });
    }
}
