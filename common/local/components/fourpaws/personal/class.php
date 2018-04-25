<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @noinspection AutoloadingIssuesInspection */
class CPersonalCabinetComponent extends CBitrixComponent
{
    /**
     * {@inheritdoc}
     * @throws \Bitrix\Main\LoaderException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);

        $arDefaultUrlTemplates404 = [
            'personal'  => '',
            'address'   => 'address/',
            'bonus'     => 'bonus/',
            'orders'    => 'orders/',
            'pets'      => 'pets/',
            'referral'  => 'referral/',
            'subscribe' => 'subscribe/',
            'top'       => 'top/',
        ];

        $arComponentVariables = [
            'SECTION_ID',
            'SECTION_CODE',
            'ELEMENT_ID',
            'ELEMENT_CODE',
        ];

        $arDefaultVariableAliases404 = [];

        $arVariables = [];

        $arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
            $arDefaultUrlTemplates404,
            $this->arParams['SEF_URL_TEMPLATES']
        );
        $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
            $arDefaultVariableAliases404,
            $this->arParams['VARIABLE_ALIASES']
        );

        $engine = new CComponentEngine($this);
        $componentPage = $engine->guessComponentPath(
            $this->arParams['SEF_FOLDER'],
            $arUrlTemplates,
            $arVariables
        );

        $hasPage = false;
        $page = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestedPage();
        if ($page === '/personal' || $page === '/personal/index.php') {
            $hasPage = true;
        } else {
            foreach ($arDefaultUrlTemplates404 as $item) {
                if (!empty($item)) {
                    $curDir = '/personal/' . substr($item, 0, -1);
                    $curPage = '/personal/' . $item . 'index.php';
                    if ($page === $curDir || $page === $curPage) {
                        $hasPage = true;
                        break;
                    }
                }
            }
        }

        if (!$hasPage) {
            $componentPage = '404';
        } else {
            if (!$componentPage) {
                $componentPage = 'personal';
            }
        }

        if ($componentPage !== '404') {
            CComponentEngine::initComponentVariables(
                $componentPage,
                $arComponentVariables,
                $arVariableAliases,
                $arVariables
            );

            /** @noinspection PhpUnusedLocalVariableInspection */
            $arResult = [
                'FOLDER'        => $this->arParams['SEF_FOLDER'],
                'URL_TEMPLATES' => $arUrlTemplates,
                'VARIABLES'     => $arVariables,
                'ALIASES'       => $arVariableAliases,
            ];

            // В режиме аватара не должно быть доступа к ЛК юзера
            /** @var \FourPaws\UserBundle\Service\UserService $userService */
            $userService =
                \FourPaws\App\Application::getInstance()
                    ->getContainer()
                    ->get(\FourPaws\UserBundle\Service\CurrentUserProviderInterface::class);
            if ($userService->isAvatarAuthorized()) {
                $componentPage = 'denied';
            }
        }

        $this->includeComponentTemplate($componentPage);

        return true;
    }
}
