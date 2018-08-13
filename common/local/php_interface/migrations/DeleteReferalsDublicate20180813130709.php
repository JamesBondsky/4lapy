<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\PersonalBundle\Entity\Referral;

class DeleteReferalsDublicate20180813130709 extends SprintMigrationBase
{

    protected $description = 'Удаление дубликатов рефералов';

    /**
     * @return bool
     */
    public function up():bool
    {
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $this->log()->error(sprintf('Ошибка загрузки контейнера: %s', $e->getMessage()));
            return false;
        }
        $referralService = $container->get('referral.service');

        $params = [
            'select' => ['ID', 'UF_CANCEL_MODERATE', 'UF_MODERATED', 'UF_USER_ID', 'UF_CARD'],
            'order'  => ['UF_USER_ID' => 'asc', 'UF_CARD' => 'asc', 'ID' => 'desc'],
        ];
        try {
            $collection = $referralService->referralRepository->findBy($params);
        } catch (ObjectPropertyException|ArgumentException|SystemException $e) {
            $this->log()->error('ошибка получения рефералов');
            return false;
        }
        $referrals = [];
        $count = $collection->count();
        $delete = [];
        if(!$collection->isEmpty()){
            $this->log()->info('Начало подготовки данных для удаления');

            /** @var Referral $item */
            foreach ($collection as $item) {
                if(!$item->isCancelModerate()){
                    if(!\in_array($item->getCard(), $referrals[$item->getUserId()], true)){
                        $referrals[$item->getUserId()][] = $item->getCard();
                    } else {
                        $delete[]=$item->getId();
                    }
                }
            }

            $this->log()->info('Подготовка данных для удаления завершена');

            if(!empty($delete)){
                $countDelete = \count($delete);
                $deleted = 0;
                $iterate = 100;
                $i = 0;

                foreach ($delete as $id) {
                    $i++;

                    try {
                        if($referralService->delete($id)){
                            $deleted++;
                        }
                    } catch (\Exception $e) {
                    }

                    if($i === $iterate){
                        $this->log()->info('Удалено '.$deleted.' из '.$countDelete.' всего '.$count);
                        $i = 0;
                    }
                }
                $this->log()->info('Удалено '.$deleted.' из '.$countDelete.' всего '.$count);
            } else {
                $this->log()->info('Не найдено дубликатов для удаления');
            }
        } else {
            $this->log()->info('Рефералов нет');
        }

        return true;
    }
}
