<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!\defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyUserDataComments;
use FourPaws\AppBundle\Exception\ErrorAddComment;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Exception\WrongEmailException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class CCommentsComponent extends \CBitrixComponent
{
    /**
     * @var DataManager $hlEntity
     */
    private $hlEntity;

    /**
     * @var UserAuthorizationInterface $userService
     */
    private $userAuthService;

    /**
     * @var CurrentUserProviderInterface $userService
     */
    private $userCurrentUserService;

    /**
     *
     * @throws WrongEmailException
     * @throws \LogicException
     * @throws ServiceNotFoundException
     * @throws LoaderException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws EmptyUserDataComments
     * @throws ErrorAddComment
     * @throws WrongPhoneNumberException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     * @return bool
     */
    public static function addComment(): bool
    {
        $class = new static();
        $class->setUserBundle();
        $class->arResult['AUTH'] = $class->userAuthService->isAuthorized();
        $data = $class->getData();
        $class->arParams['HL_ID'] = $data['HL_ID'];
        $class->arParams['OBJECT_ID'] = $data['UF_OBJECT_ID'];
        unset($data['HL_ID']);

        $class->setHLEntity();
        $res = $class->hlEntity::add($data);
        if ($res->isSuccess()) {
            return true;
        }

        throw new ErrorAddComment(
            'Произошла ошибка при добавлении комментария ' . implode('<br/>', $res->getErrorMessages())
        );
    }

    /**
     * @param int $hlID
     *
     * @throws \LogicException
     * @throws LoaderException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws \RuntimeException
     * @return mixed
     */
    public static function getHLEntity(int $hlID)
    {
        /** @todo Расширить Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory методом createTableObjectById */
        Loader::includeModule('highloadblock');

        $result = HighloadBlockTable::query()->setSelect(['*'])->setFilter(['ID' => $hlID])->exec();

        if ($result->getSelectedRowsCount() > 1) {
            throw new \LogicException('Неверный фильтр: найдено несколько HLBlock.');
        }

        $hlBlockFields = $result->fetch();

        if (!\is_array($hlBlockFields)) {
            throw new \RuntimeException('HLBlock не найден.');
        }

        $dataManager = HighloadBlockTable::compileEntity($hlBlockFields)->getDataClass();

        if (\is_string($dataManager)) {
            return new $dataManager;
        }

        if (\is_object($dataManager)) {
            return $dataManager;
        }

        throw new \RuntimeException('Ошибка компиляции сущности для HLBlock.');
    }

    /**
     * @throws \LogicException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @return array
     */
    public static function getNextItems(): array
    {
        $class = new static();
        $class->setUserBundle();
        $request = Application::getInstance()->getContext()->getRequest();
        $class->arParams['HL_ID'] = $request->get('hl_id');
        $class->arParams['OBJECT_ID'] = $request->get('object_id');
        $class->arParams['TYPE'] = $request->get('type');
        $class->arParams['ITEMS_COUNT'] = $request->get('items_count');
        $class->arParams['PAGE'] = $request->get('page');
        $class->arParams['SORT_DESC'] = $request->get('sort_desc');
        $class->arParams['ACTIVE_DATE_FORMAT'] = $request->get('active_date_format') ?? 'd.m.Y';
        $class->setHLEntity();
        $items = $class->getComments();

        return $items['ITEMS'];
    }

    /**
     * {@inheritdoc}
     */
    public function onPrepareComponentParams($params): array
    {
        $params['HL_ID'] = (int)$params['HL_ID'];
        $params['OBJECT_ID'] = (int)$params['OBJECT_ID'];
        $params['SORT_DESC'] = !empty($params['SORT_DESC']) ? $params['SORT_DESC'] : 'Y';
        $params['ITEMS_COUNT'] = (int)$params['ITEMS_COUNT'] <= 0 ? (int)$params['ITEMS_COUNT'] : 5;
        $params['ACTIVE_DATE_FORMAT'] = trim($params['ACTIVE_DATE_FORMAT']);
        $params['ACTIVE_DATE_FORMAT'] =
            \strlen($params['ACTIVE_DATE_FORMAT']) <= 0 ? $params['ACTIVE_DATE_FORMAT'] : Date::getFormat();
        if (empty($params['TYPE'])) {
            $params['TYPE'] = 'iblock';
        }

        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?: 360000;

        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws ServiceNotFoundException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        if ($this->arParams['HL_ID'] === 0) {
            ShowError('Не выбран HL блок комментариев');

            return false;
        }
        if ($this->arParams['OBJECT_ID'] === 0) {
            ShowError('Не выбран объект комментирования');

            return false;
        }

        /** @todo кеширование комментариев */
        if ($this->startResultCache()) {

            try {
                $this->setHLEntity();
            } catch (LoaderException $e) {
                ShowError($e->getMessage());

                return false;
            } catch (SystemException $e) {
                ShowError($e->getMessage());

                return false;
            }
            try {
                $this->setUserBundle();
            } catch (ApplicationCreateException $e) {
                ShowError($e->getMessage());

                return false;
            } catch (ServiceCircularReferenceException $e) {
                ShowError($e->getMessage());

                return false;
            }
            $this->arResult['AUTH'] = $this->userAuthService->isAuthorized();

            try {
                $comments = $this->getComments();
                $this->arResult['COMMENTS'] = $comments['ITEMS'];
                $this->arResult['COUNT_COMMENTS'] = $comments['COUNT'];
            } catch (ArgumentException $e) {
                ShowError($e->getMessage());

                return false;
            }
            $this->arResult['RATING'] = $this->getRating();

            TaggedCacheHelper::addManagedCacheTags([
                'comments:objectId:' . $this->arParams['OBJECT_ID'],
                'comments:type:' . $this->arParams['TYPE'],
                'hlb:field:comments_objectId:' . $this->arParams['OBJECT_ID'],
                'catalog:comments',
            ]);

            $this->includeComponentTemplate();
        }

        return true;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    protected function setUserBundle()
    {
        $this->userAuthService = App::getInstance()->getContainer()->get(UserAuthorizationInterface::class);
        $this->userCurrentUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
    }

    /**
     * @throws SystemException
     * @throws EmptyUserDataComments
     * @throws ErrorAddComment
     * @throws WrongPhoneNumberException
     * @throws WrongEmailException
     * @return array
     */
    protected function getData(): array
    {
        $data = Application::getInstance()->getContext()->getRequest()->getPostList()->toArray();
        unset($data['action']);
        if ($this->arResult['AUTH']) {
            $data['UF_USER_ID'] = $this->userCurrentUserService->getCurrentUserId();
        } else {
            $userRepository = $this->userCurrentUserService->getUserRepository();
            $filter = [
                'LOGIC' => 'OR',
            ];
            if (!empty($data['EMAIL'])) {
                if (filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
                    throw new WrongEmailException(
                        'Введен некорректный email'
                    );
                }
                $filter[] = [
                    '=EMAIL' => $data['EMAIL'],
                ];
            }
            if (!empty($data['PHONE']) && PhoneHelper::isPhone($data['PHONE'])) {
                $filter[] = [
                    '=PERSONAL_PHONE' => PhoneHelper::normalizePhone($data['PHONE']),
                ];
            }
            if (count($filter) > 1) {
                $users = $userRepository->findBy($filter);
                if (\is_array($users) && !empty($users)) {
                    foreach ($users as $user) {
                        if ($user->equalPassword($data['PASSWORD'])) {
                            $data['UF_USER_ID'] = $user->getId();
                            break;
                        }
                    }
                }
                if (empty($data['UF_USER_ID'])) {
                    throw new ErrorAddComment(
                        'Пользователь не найден, либо данные введены неверно'
                    );
                }
            } else {
                throw new EmptyUserDataComments('Не указаны параметры');
            }
        }
        unset($data['PHONE'], $data['EMAIL'], $data['PASSWORD']);
        $data['UF_ACTIVE'] = 0;
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $data['UF_DATE'] = new Date();

        return $data;
    }

    /**
     * @throws \LogicException
     * @throws LoaderException
     * @throws SystemException
     * @throws \RuntimeException
     */
    protected function setHLEntity()
    {
        $this->hlEntity = static::getHLEntity($this->arParams['HL_ID']);
    }

    /**
     * @throws ArgumentException
     * @return array
     */
    protected function getComments(): array
    {
        $query = $this->hlEntity::query();
        $query->setSelect(['*']);
        $query->setFilter(
            [
                'UF_OBJECT_ID' => $this->arParams['OBJECT_ID'],
                'UF_TYPE'      => $this->arParams['TYPE'],
                'UF_ACTIVE'    => 1,
            ]
        );
        $query->setOrder(
            [
                'UF_DATE' => ($this->arParams['SORT_DESC'] === 'Y') ? 'desc' : 'asc',
                'ID'      => ($this->arParams['SORT_DESC'] === 'Y') ? 'desc' : 'asc',
            ]
        );
        $query->countTotal(true);
        if ($this->arParams['ITEMS_COUNT'] > 0) {
            $query->setLimit($this->arParams['ITEMS_COUNT']);
        }
        if ((int)$this->arParams['PAGE'] > 0) {
            $query->setOffset($this->arParams['ITEMS_COUNT'] * (int)$this->arParams['PAGE']);
        }

        $res = $query->exec();
        $items = [];
        $userIds = [];
        while ($item = $res->fetch()) {
            if ($item['UF_DATE'] instanceof Date) {
                $item['DATE_FORMATED'] = $item['UF_DATE']->format($this->arParams['ACTIVE_DATE_FORMAT']);
            }
            if ((int)$item['UF_USER_ID'] > 0) {
                $userIds[$item['ID']] = (int)$item['UF_USER_ID'];
            } else {
                $item['USER_NAME'] = 'Анонимно';
            }
            $items[$item['ID']] = $item;
        }
        if (!empty($userIds)) {
            $users = $this->userCurrentUserService->getUserRepository()->findBy(['ID' => array_unique($userIds)]);
            if (\is_array($users) && !empty($users)) {
                foreach ($users as $user) {
                    foreach ($userIds as $itemID => $userID) {
                        if ($userID === $user->getId()) {
                            $items[$itemID]['USER_NAME'] = $user->getFullName();
                            unset($userIds[$itemID]);
                        }
                    }
                }
            }
        }

        return [
            'ITEMS' => $items,
            'COUNT' => $res->getCount(),
        ];
    }

    /**
     * @return int
     */
    protected function getRating(): int
    {
        $rating = 0;
        if (\is_array($this->arResult['COMMENTS']) && !empty($this->arResult['COMMENTS'])) {
            $rating = $this->getSumMarkComments() / $this->arResult['COUNT_COMMENTS'];
        }

        return $rating;
    }

    /**
     * @return int
     */
    protected function getSumMarkComments(): int
    {
        $query = $this->hlEntity::query();
        $query->setSelect(['SUM']);
        $query->setFilter(
            [
                'UF_OBJECT_ID' => $this->arParams['OBJECT_ID'],
                'UF_TYPE'      => $this->arParams['TYPE'],
                'UF_ACTIVE'    => 1,
            ]
        );
        $query->registerRuntimeField(
            'SUM',
            new ExpressionField(
                'SUM',
                'SUM(%s)',
                ['UF_MARK']
            )
        );

        return (int)$query->exec()->fetch()['SUM'];
    }
}
